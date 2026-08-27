from pydantic import BaseModel, Field


class DocumentUploadResponse(BaseModel):

    success: bool

    message: str

    filename: str

    document_id: str

    file_type: str

    text_length: int


class KnowledgeSearchRequest(BaseModel):

    query: str = Field(
        ...,
        min_length=1,
        max_length=2000,
    )

    top_k: int = Field(
        default=5,
        ge=1,
        le=20,
    )