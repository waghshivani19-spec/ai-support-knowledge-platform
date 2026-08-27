from fastapi import APIRouter, File, HTTPException, UploadFile

from app.schemas.knowledge import (
    DocumentUploadResponse,
    KnowledgeSearchRequest,
)
from app.services.chunking_service import ChunkingService
from app.services.document_service import DocumentService
from app.services.embedding_service import EmbeddingService
from app.services.vector_service import VectorService




router = APIRouter(
    prefix="/knowledge",
    tags=["Knowledge Base"],
)


@router.post(
    "/upload",
    response_model=DocumentUploadResponse,
)
async def upload_document(
    file: UploadFile = File(...),
):
    """
    Complete knowledge ingestion pipeline.

    Document
        ↓
    Text extraction
        ↓
    Chunking
        ↓
    Embeddings
        ↓
    ChromaDB
    """

    try:

        file_content = await file.read()

        document_service = (
            DocumentService()
        )

        result = (
            document_service.process_document(
                filename=file.filename or "unknown",
                file_content=file_content,
            )
        )

        text = result["text"]

        document_id = result[
            "document_id"
        ]

        # -------------------------
        # 1. Chunking
        # -------------------------

        chunking_service = (
            ChunkingService()
        )

        chunks = (
            chunking_service.create_chunks(
                text
            )
        )

        if not chunks:
            raise ValueError(
                "No chunks were generated."
            )

        # -------------------------
        # 2. Embeddings
        # -------------------------

        embedding_service = (
            EmbeddingService()
        )

        embeddings = (
            embedding_service.generate_embeddings(
                chunks
            )
        )

        # -------------------------
        # 3. Vector database
        # -------------------------

        vector_service = (
            VectorService()
        )

        chunk_ids = [
            f"{document_id}_chunk_{index}"
            for index in range(len(chunks))
        ]

        metadatas = [
            {
                "document_id": document_id,
                "filename": result[
                    "original_filename"
                ],
                "file_type": result[
                    "file_type"
                ],
                "chunk_index": index,
            }
            for index in range(len(chunks))
        ]

        vector_service.add_chunks(
            chunk_ids=chunk_ids,
            texts=chunks,
            embeddings=embeddings,
            metadatas=metadatas,
        )

        return DocumentUploadResponse(
            success=True,
            message=(
                "Document uploaded and "
                "indexed successfully"
            ),
            filename=result[
                "original_filename"
            ],
            document_id=document_id,
            file_type=result["file_type"],
            text_length=result["text_length"],
        )

    except ValueError as exc:

        raise HTTPException(
            status_code=400,
            detail=str(exc),
        )

    except Exception as exc:

        raise HTTPException(
            status_code=500,
            detail=(
                "An unexpected error occurred "
                "while processing the document."
            ),
        ) from exc


@router.post("/search")
def search_knowledge(
    request: KnowledgeSearchRequest,
):
    try:

        embedding_service = (
            EmbeddingService()
        )

        query_embedding = (
            embedding_service.generate_embedding(
                request.query
            )
        )

        vector_service = (
            VectorService()
        )

        results = vector_service.search(
            query_embedding=query_embedding,
            top_k=request.top_k,
        )

        return {
            "success": True,
            "query": request.query,
            "results": results,
        }

    except Exception as exc:

        raise HTTPException(
            status_code=500,
            detail=str(exc),
        )