# app/api/health.py

from fastapi import APIRouter

from app.core.config import settings


router = APIRouter(
    prefix="/health",
    tags=["Health"],
)


@router.get("")
def health_check():
    return {
        "success": True,
        "status": "healthy",
        "service": settings.APP_NAME,
        "environment": settings.APP_ENV,
    }