import logging

from fastapi import APIRouter, HTTPException

from app.core.config import settings
from app.schemas.ai import AIChatRequest, AIChatResponse
from app.services.rag_service import RAGService


logger = logging.getLogger(__name__)


router = APIRouter(
    prefix="/ai",
    tags=["AI"],
)


@router.post(
    "/chat",
    response_model=AIChatResponse,
)
def chat(request: AIChatRequest):

    try:

        logger.info(
            "AI chat question: %s",
            request.message,
        )

        rag_service = RAGService()

        result = rag_service.ask(
            question=request.message,
        )

        return AIChatResponse(
            success=True,
            message="AI response generated successfully",
            response=result["answer"],
            model=settings.OLLAMA_MODEL,
        )

    except ValueError as exc:

        raise HTTPException(
            status_code=400,
            detail=str(exc),
        )

    except Exception as exc:

        logger.exception(
            "AI chat request failed"
        )

        raise HTTPException(
            status_code=500,
            detail=str(exc),
        )