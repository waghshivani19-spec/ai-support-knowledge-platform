from fastapi import APIRouter, HTTPException
from pydantic import BaseModel

from app.services.embedding_service import EmbeddingService


router = APIRouter(
    prefix="/embeddings",
    tags=["Embeddings"],
)


class EmbeddingRequest(BaseModel):
    text: str


@router.post("")
def create_embedding(
    request: EmbeddingRequest,
):

    try:

        service = EmbeddingService()

        embedding = service.generate_embedding(
            request.text
        )

        return {
            "success": True,
            "dimensions": len(embedding),
            "embedding": embedding,
        }

    except Exception as exc:

        raise HTTPException(
            status_code=500,
            detail=str(exc),
        )