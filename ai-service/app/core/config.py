from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    APP_NAME: str = "ai-support-service"

    APP_ENV: str = "local"

    APP_HOST: str = "127.0.0.1"

    APP_PORT: int = 8001

    DEBUG: bool = True

    LARAVEL_API_URL: str = "http://127.0.0.1:8000"

    LARAVEL_API_TOKEN: str = ""

    OLLAMA_BASE_URL: str = "http://127.0.0.1:11434"

    OLLAMA_MODEL: str = "gemma4"

    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        case_sensitive=True,
        extra="ignore",
    )


settings = Settings()