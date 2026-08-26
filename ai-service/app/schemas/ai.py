
from pydantic import BaseModel, Field


class AIChatRequest(BaseModel):

    message: str = Field(
        ...,
        min_length=1,
        max_length=5000,
    )


class AIChatResponse(BaseModel):

    success: bool

    message: str

    response: str

    model: str