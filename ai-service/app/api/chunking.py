from fastapi import APIRouter

from app.services.chunking_service import ChunkingService


router = APIRouter(
    prefix="/chunking",
    tags=["Chunking"],
)


@router.post("")
def create_chunks(text: str):
    service = ChunkingService()

    chunks = service.create_chunks(text)

    return {
        "success": True,
        "chunk_count": len(chunks),
        "chunks": chunks,
    }