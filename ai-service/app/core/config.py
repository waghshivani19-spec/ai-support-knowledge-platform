from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    """
    Application-wide configuration.

    Values are loaded from environment variables
    and the .env file.
    """

    # Application
    APP_NAME: str = "ai-support-service"
    APP_ENV: str = "local"
    APP_HOST: str = "127.0.0.1"
    APP_PORT: int = 8001
    DEBUG: bool = True

    # Laravel
    LARAVEL_API_URL: str = "http://127.0.0.1:8000"
    LARAVEL_API_TOKEN: str = ""

    # Ollama
    OLLAMA_BASE_URL: str = "http://127.0.0.1:11434"
    OLLAMA_MODEL: str = "gemma4"
    OLLAMA_EMBEDDING_MODEL: str = "nomic-embed-text"

    # Document storage
    DOCUMENT_STORAGE_PATH: str = "data/documents"
    MAX_UPLOAD_SIZE_MB: int = 10

    CHUNK_SIZE: int = 700
    CHUNK_OVERLAP: int = 100


    RAG_TOP_K: int = 5
    RAG_DISTANCE_THRESHOLD: float = 0.80
    VECTOR_STORE_PATH: str = "data/vector_store"


    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        case_sensitive=True,
        extra="ignore",
    )


settings = Settings()