from ollama import Client

from app.core.config import settings


class AIService:

    def __init__(self):
        self.client = Client(
            host=settings.OLLAMA_BASE_URL
        )

    def generate_response(self, message: str) -> str:

        response = self.client.chat(
            model=settings.OLLAMA_MODEL,
            messages=[
                {
                    "role": "user",
                    "content": message,
                }
            ],
        )

        return response.message.content