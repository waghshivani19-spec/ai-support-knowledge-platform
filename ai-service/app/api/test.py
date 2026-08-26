from fastapi import APIRouter

router = APIRouter(
    prefix="/test",
    tags=["Test"],
)


@router.get("")
def test_connection():
    return {
        "success": True,
        "message": "Laravel successfully connected to FastAPI",
        "service": "ai-support-service",
    }