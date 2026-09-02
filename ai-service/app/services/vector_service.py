from pathlib import Path

import chromadb

from app.core.config import settings


class VectorService:
    """
    Handles storage and retrieval of document embeddings
    using ChromaDB.
    """

    COLLECTION_NAME = "knowledge_base"

    def __init__(self):

        # Use the configured vector store path
        storage_path = Path(
            settings.VECTOR_STORE_PATH
        )

        storage_path.mkdir(
            parents=True,
            exist_ok=True,
        )

        # Persistent ChromaDB client
        self.client = chromadb.PersistentClient(
            path=str(storage_path)
        )

        # Get or create knowledge-base collection
        self.collection = (
            self.client.get_or_create_collection(
                name=self.COLLECTION_NAME,
                metadata={
                    "description": (
                        "AI Support Knowledge Base"
                    )
                },
            )
        )

    # --------------------------------------------------
    # ADD / UPDATE DOCUMENT CHUNKS
    # --------------------------------------------------

    def add_chunks(
        self,
        chunk_ids: list[str],
        texts: list[str],
        embeddings: list[list[float]],
        metadatas: list[dict],
    ) -> None:
        """
        Store document chunks and their embeddings
        in ChromaDB.
        """

        if not (
            len(chunk_ids)
            == len(texts)
            == len(embeddings)
            == len(metadatas)
        ):
            raise ValueError(
                "Chunk IDs, texts, embeddings and "
                "metadata must have the same length."
            )

        if not chunk_ids:
            return

        self.collection.upsert(
            ids=chunk_ids,
            documents=texts,
            embeddings=embeddings,
            metadatas=metadatas,
        )

    # --------------------------------------------------
    # SEMANTIC SEARCH
    # --------------------------------------------------

    def search(
        self,
        query_embedding: list[float],
        top_k: int = 5,
    ) -> dict:
        """
        Search the knowledge base using a query embedding.

        Returns:
            documents
            metadatas
            ids
            distances
        """

        if not query_embedding:
            raise ValueError(
                "Query embedding cannot be empty."
            )

        if top_k < 1:
            raise ValueError(
                "top_k must be at least 1."
            )

        results = self.collection.query(
            query_embeddings=[
                query_embedding
            ],
            n_results=top_k,
            include=[
                "documents",
                "metadatas",
                "distances",
            ],
        )

        return results


    def delete_document(
        self,
        document_id: str,
    ) -> None:

        self.collection.delete(
            where={
                "document_id": document_id
            }
        )


    # --------------------------------------------------
    # COUNT
    # --------------------------------------------------

    def count(self) -> int:
        """
        Return the total number of stored chunks.
        """

        return self.collection.count()