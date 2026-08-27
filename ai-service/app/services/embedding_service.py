from ollama import Client

from app.core.config import settings


class EmbeddingService:
    """
    Generates vector embeddings using Ollama.
    """

    def __init__(self):
        self.client = Client(
            host=settings.OLLAMA_BASE_URL
        )

    def generate_embedding(
        self,
        text: str,
    ) -> list[float]:

        if not text.strip():
            raise ValueError(
                "Cannot create an embedding from empty text."
            )

        response = self.client.embed(
            model=settings.OLLAMA_EMBEDDING_MODEL,
            input=text,
        )

        return response["embeddings"][0]

    def generate_embeddings(
        self,
        texts: list[str],
    ) -> list[list[float]]:

        if not texts:
            return []

        response = self.client.embed(
            model=settings.OLLAMA_EMBEDDING_MODEL,
            input=texts,
        )

        return response["embeddings"]