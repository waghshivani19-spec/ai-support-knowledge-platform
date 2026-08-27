from fastapi import APIRouter

from app.services.embedding_service import EmbeddingService
from app.services.vector_service import VectorService


router = APIRouter(
    prefix="/vector",
    tags=["Vector Database"],
)


@router.post("/test")
def vector_test():

    text = (
        "Employees can reset their company "
        "password using the internal password portal."
    )

    embedding_service = EmbeddingService()

    vector_service = VectorService()

    embedding = (
        embedding_service.generate_embedding(
            text
        )
    )

    vector_service.add_chunks(
        chunk_ids=["test_chunk_001"],
        texts=[text],
        embeddings=[embedding],
        metadatas=[
            {
                "document_id": "test_document",
                "filename": "test.txt",
                "chunk_index": 0,
            }
        ],
    )

    return {
        "success": True,
        "message": "Vector stored successfully",
        "vector_dimensions": len(embedding),
        "total_vectors": vector_service.count(),
    }