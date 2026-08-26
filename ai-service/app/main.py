# app/main.py

from fastapi import FastAPI

from app.api.health import router as health_router
from app.api.test import router as test_router
from app.api.ai import router as ai_router

app = FastAPI(
    title="AI Service",
    description="AI microservice API",
    version="1.0.0",
)


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