from app.core.config import settings


class ChunkingService:
    """
    Splits extracted document text into smaller overlapping chunks.
    """

    def __init__(
        self,
        chunk_size: int | None = None,
        chunk_overlap: int | None = None,
    ):
        self.chunk_size = (
            chunk_size
            if chunk_size is not None
            else settings.CHUNK_SIZE
        )

        self.chunk_overlap = (
            chunk_overlap
            if chunk_overlap is not None
            else settings.CHUNK_OVERLAP
        )

        if self.chunk_overlap >= self.chunk_size:
            raise ValueError(
                "Chunk overlap must be smaller than chunk size."
            )

    def clean_text(self, text: str) -> str:
        """
        Basic text cleaning.
        """

        lines = [
            line.strip()
            for line in text.splitlines()
            if line.strip()
        ]

        return "\n".join(lines)

    def create_chunks(self, text: str) -> list[str]:
        """
        Split text into overlapping chunks.
        """

        text = self.clean_text(text)

        if not text:
            return []

        chunks = []

        start = 0
        text_length = len(text)

        while start < text_length:

            end = start + self.chunk_size

            chunk = text[start:end].strip()

            if chunk:
                chunks.append(chunk)

            if end >= text_length:
                break

            start = end - self.chunk_overlap

        return chunks