from fastapi import APIRouter, HTTPException

from app.core.config import settings
from app.schemas.ai import AIChatRequest, AIChatResponse
from app.services.ai_service import AIService


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
        service = AIService()

        response = service.generate_response(
            request.message
        )

        return AIChatResponse(
            success=True,
            message="AI response generated successfully",
            response=response,
            model=settings.OLLAMA_MODEL,
        )

    except Exception as exc:

        raise HTTPException(
            status_code=500,
            detail=str(exc),
        )