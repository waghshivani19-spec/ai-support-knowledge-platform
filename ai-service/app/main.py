# app/main.py

from fastapi import FastAPI

from app.api.health import router as health_router
from app.api.test import router as test_router
from app.api.ai import router as ai_router
from app.api.knowledge import router as knowledge_router
from app.api.chunking import router as chunking_router
from app.api.embeddings import router as embeddings_router
from app.api.vector import router as vector_router
import logging

app = FastAPI(
    title="AI Service",
    description="AI microservice API",
    version="1.0.0",
)


logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s"
)

logger = logging.getLogger(__name__)

# Register API routes
app.include_router(
    health_router,
    prefix="/api",
)

app.include_router(
    test_router,
    prefix="/api",
)

app.include_router(
    knowledge_router,
    prefix="/api",
)

app.include_router(
    chunking_router,
    prefix="/api",
)

app.include_router(
    embeddings_router,
    prefix="/api",
)

app.include_router(
    vector_router,
    prefix="/api",
)

app.include_router(
    ai_router,
    prefix="/api",
)

@app.get("/")
def root():
    return {
        "success": True,
        "message": "AI Service is running",
        "version": "1.0.0",
    }