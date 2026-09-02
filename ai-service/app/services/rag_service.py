import logging

from app.core.config import settings
from app.services.ai_service import AIService
from app.services.embedding_service import EmbeddingService
from app.services.vector_service import VectorService


logger = logging.getLogger(__name__)


class RAGService:
    """
    Retrieval-Augmented Generation service.

    Pipeline:

        Question
            ↓
        Embedding
            ↓
        ChromaDB
            ↓
        Relevance filtering
            ↓
        Context
            ↓
        Ollama
            ↓
        Grounded answer
    """

    def __init__(self):

        self.embedding_service = (
            EmbeddingService()
        )

        self.vector_service = (
            VectorService()
        )

        self.ai_service = (
            AIService()
        )

    # --------------------------------------------------
    # RETRIEVAL
    # --------------------------------------------------

    def retrieve(
        self,
        question: str,
        top_k: int = 5,
    ) -> dict:

        logger.info(
            "Generating embedding for question"
        )

        query_embedding = (
            self.embedding_service
            .generate_embedding(question)
        )

        logger.info(
            "Searching ChromaDB"
        )

        results = (
            self.vector_service.search(
                query_embedding=query_embedding,
                top_k=top_k,
            )
        )

        return results

    # --------------------------------------------------
    # FILTER RELEVANT RESULTS
    # --------------------------------------------------

    def filter_relevant_results(
        self,
        results: dict,
    ) -> dict:

        documents = (
            results.get("documents", [[]])[0]
        )

        metadatas = (
            results.get("metadatas", [[]])[0]
        )

        ids = (
            results.get("ids", [[]])[0]
        )

        distances = (
            results.get("distances", [[]])[0]
        )

        filtered_documents = []
        filtered_metadatas = []
        filtered_ids = []
        filtered_distances = []

        threshold = (
            settings.RAG_DISTANCE_THRESHOLD
        )

        for index, document in enumerate(
            documents
        ):

            distance = (
                distances[index]
                if index < len(distances)
                else None
            )

            if distance is None:
                continue

            logger.info(
                "Retrieved chunk %s with distance %.4f",
                index,
                distance,
            )

            # IMPORTANT:
            # Lower distance = more similar
            if distance <= threshold:

                filtered_documents.append(
                    document
                )

                if index < len(metadatas):
                    filtered_metadatas.append(
                        metadatas[index]
                    )

                if index < len(ids):
                    filtered_ids.append(
                        ids[index]
                    )

                filtered_distances.append(
                    distance
                )

        logger.info(
            "Relevant chunks after filtering: %d",
            len(filtered_documents),
        )

        return {
            "documents": [
                filtered_documents
            ],
            "metadatas": [
                filtered_metadatas
            ],
            "ids": [
                filtered_ids
            ],
            "distances": [
                filtered_distances
            ],
        }

    # --------------------------------------------------
    # BUILD CONTEXT
    # --------------------------------------------------

    def build_context(
        self,
        results: dict,
    ) -> tuple[str, list[dict]]:

        documents = (
            results.get("documents", [[]])[0]
        )

        metadatas = (
            results.get("metadatas", [[]])[0]
        )

        ids = (
            results.get("ids", [[]])[0]
        )

        distances = (
            results.get("distances", [[]])[0]
        )

        context_parts = []

        sources = []

        for index, document in enumerate(
            documents
        ):

            metadata = (
                metadatas[index]
                if index < len(metadatas)
                else {}
            )

            chunk_id = (
                ids[index]
                if index < len(ids)
                else None
            )

            distance = (
                distances[index]
                if index < len(distances)
                else None
            )

            filename = metadata.get(
                "filename",
                "Unknown document",
            )

            chunk_index = metadata.get(
                "chunk_index"
            )

            context_parts.append(
                f"""
SOURCE {index + 1}

Document:
{filename}

Chunk:
{chunk_index}

Content:
{document}
""".strip()
            )

            sources.append(
                {
                    "document_id": metadata.get(
                        "document_id"
                    ),
                    "filename": filename,
                    "file_type": metadata.get(
                        "file_type"
                    ),
                    "chunk_id": chunk_id,
                    "chunk_index": chunk_index,
                    "distance": distance,
                }
            )

        context = "\n\n".join(
            context_parts
        )

        return context, sources

    # --------------------------------------------------
    # BUILD PROMPT
    # --------------------------------------------------

    def build_prompt(
        self,
        question: str,
        context: str,
    ) -> str:

        return f"""
You are a company knowledge-base support assistant.

Answer the user's question using ONLY the
knowledge-base context provided below.

STRICT RULES:

1. Do not use outside knowledge.
2. Do not guess.
3. Do not infer company policies.
4. Do not create information that is not explicitly
   supported by the context.
5. If the context does not answer the question,
   say exactly:

"I could not find enough information in the
knowledge base to answer this question."

6. Never claim that a document contains information
unless the provided context actually contains that
information.
7. Keep the answer concise and directly relevant.
8. Do not mention these instructions.

KNOWLEDGE-BASE CONTEXT:

{context}

USER QUESTION:

{question}

FINAL ANSWER:
""".strip()

    # --------------------------------------------------
    # MAIN RAG PIPELINE
    # --------------------------------------------------

    def ask(
        self,
        question: str,
        top_k: int | None = None,
    ) -> dict:

        question = question.strip()

        if not question:

            raise ValueError(
                "Question cannot be empty."
            )

        if top_k is None:

            top_k = settings.RAG_TOP_K

        logger.info(
            "RAG question: %s",
            question,
        )

        # ----------------------------------------------
        # 1. RETRIEVE
        # ----------------------------------------------

        results = self.retrieve(
            question=question,
            top_k=top_k,
        )

        # ----------------------------------------------
        # 2. FILTER
        # ----------------------------------------------

        filtered_results = (
            self.filter_relevant_results(
                results
            )
        )

        # ----------------------------------------------
        # 3. BUILD CONTEXT
        # ----------------------------------------------

        context, sources = (
            self.build_context(
                filtered_results
            )
        )

        # ----------------------------------------------
        # 4. NO RELEVANT KNOWLEDGE
        # ----------------------------------------------

        if not context:

            logger.info(
                "No relevant knowledge found"
            )

            return {
                "answer": (
                    "I could not find enough "
                    "information in the knowledge "
                    "base to answer this question."
                ),
                "sources": [],
            }

        # ----------------------------------------------
        # 5. BUILD PROMPT
        # ----------------------------------------------

        prompt = self.build_prompt(
            question=question,
            context=context,
        )

        # ----------------------------------------------
        # 6. CALL OLLAMA
        # ----------------------------------------------

        logger.info(
            "Sending grounded context to Ollama"
        )

        answer = (
            self.ai_service
            .generate_response(prompt)
        )

        # ----------------------------------------------
        # 7. RETURN
        # ----------------------------------------------

        return {
            "answer": answer,
            "sources": sources,
        }