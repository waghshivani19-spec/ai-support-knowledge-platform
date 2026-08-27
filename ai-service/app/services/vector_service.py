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

        storage_path = Path(
            "data/vector_store"
        )

        storage_path.mkdir(
            parents=True,
            exist_ok=True,
        )

        self.client = chromadb.PersistentClient(
            path=str(storage_path)
        )

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

    def add_chunks(
        self,
        chunk_ids: list[str],
        texts: list[str],
        embeddings: list[list[float]],
        metadatas: list[dict],
    ) -> None:

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

    def search(
        self,
        query_embedding: list[float],
        top_k: int = 5,
    ) -> dict:

        return self.collection.query(
            query_embeddings=[
                query_embedding
            ],
            n_results=top_k,
        )

    def count(self) -> int:
        return self.collection.count()