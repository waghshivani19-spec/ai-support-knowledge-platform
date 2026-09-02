from io import BytesIO
from pathlib import Path

import fitz
from docx import Document

from app.core.config import settings


class DocumentService:
    """
    Handles document validation, storage and text extraction.

    The document_id is provided by Laravel.
    """

    ALLOWED_EXTENSIONS = {
        ".txt",
        ".pdf",
        ".docx",
    }

    def __init__(self):
        self.storage_path = Path(
            settings.DOCUMENT_STORAGE_PATH
        )

        self.storage_path.mkdir(
            parents=True,
            exist_ok=True,
        )

    def validate_extension(
        self,
        filename: str,
    ) -> str:
        """
        Validate the file extension and return it.
        """

        extension = Path(filename).suffix.lower()

        if extension not in self.ALLOWED_EXTENSIONS:
            raise ValueError(
                f"Unsupported file type: {extension}. "
                f"Allowed types: "
                f"{', '.join(self.ALLOWED_EXTENSIONS)}"
            )

        return extension

    def validate_size(
        self,
        file_content: bytes,
    ) -> None:
        """
        Validate the uploaded file size.
        """

        max_size = (
            settings.MAX_UPLOAD_SIZE_MB
            * 1024
            * 1024
        )

        if len(file_content) > max_size:
            raise ValueError(
                f"File size exceeds the maximum "
                f"allowed size of "
                f"{settings.MAX_UPLOAD_SIZE_MB} MB."
            )

    def extract_text(
        self,
        file_content: bytes,
        extension: str,
    ) -> str:
        """
        Extract text depending on file type.
        """

        if extension == ".txt":
            return self._extract_txt(
                file_content
            )

        if extension == ".pdf":
            return self._extract_pdf(
                file_content
            )

        if extension == ".docx":
            return self._extract_docx(
                file_content
            )

        raise ValueError(
            f"Unsupported file type: {extension}"
        )

    def _extract_txt(
        self,
        file_content: bytes,
    ) -> str:
        """
        Extract text from TXT file.
        """

        return file_content.decode(
            "utf-8",
            errors="replace",
        )

    def _extract_pdf(
        self,
        file_content: bytes,
    ) -> str:
        """
        Extract text from PDF file.
        """

        text_parts = []

        with fitz.open(
            stream=file_content,
            filetype="pdf",
        ) as pdf:

            for page in pdf:
                text_parts.append(
                    page.get_text()
                )

        return "\n".join(text_parts)

    def _extract_docx(
        self,
        file_content: bytes,
    ) -> str:
        """
        Extract text from DOCX file.
        """

        document = Document(
            BytesIO(file_content)
        )

        paragraphs = []

        for paragraph in document.paragraphs:

            text = paragraph.text.strip()

            if text:
                paragraphs.append(text)

        return "\n".join(paragraphs)

    def save_file(
        self,
        document_id: str,
        file_content: bytes,
        extension: str,
    ) -> str:
        """
        Save the original document using the
        document_id provided by Laravel.

        Returns:
            stored_filename
        """

        stored_filename = (
            f"{document_id}{extension}"
        )

        file_path = (
            self.storage_path
            / stored_filename
        )

        file_path.write_bytes(
            file_content
        )

        return stored_filename

    def process_document(
        self,
        document_id: str,
        filename: str,
        file_content: bytes,
    ) -> dict:
        """
        Complete document processing flow.

        1. Validate extension
        2. Validate file size
        3. Extract text
        4. Save original file
        5. Return metadata

        The document_id is provided by Laravel.
        """

        document_id = document_id.strip()

        if not document_id:
            raise ValueError(
                "document_id cannot be empty."
            )

        extension = self.validate_extension(
            filename
        )

        self.validate_size(
            file_content
        )

        text = self.extract_text(
            file_content,
            extension,
        )

        if not text.strip():
            raise ValueError(
                "No readable text was found "
                "in the document."
            )

        stored_filename = self.save_file(
            document_id=document_id,
            file_content=file_content,
            extension=extension,
        )

        return {
            "document_id": document_id,
            "original_filename": filename,
            "stored_filename": stored_filename,
            "file_type": extension.lstrip("."),
            "text": text,
            "text_length": len(text),
        }