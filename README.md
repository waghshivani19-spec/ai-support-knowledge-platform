# AI Support Knowledge Platform

A Laravel-based backend platform for managing company support knowledge, documents, users, and access permissions.

The goal of this project is to provide a centralized knowledge system that can later be enhanced with AI-powered search and support assistance.

---

## 📌 What is this project?

Companies often have a large amount of information spread across:

- FAQs
- Product documentation
- Support guides
- Troubleshooting documents
- Internal knowledge articles
- Customer support information

Finding the right information manually can take a lot of time.

The **AI Support Knowledge Platform** provides a centralized place where companies can organize this information into knowledge bases and manage related documents.

The platform is designed as a foundation for an AI-powered support system where users will eventually be able to ask questions and receive answers based on the company's knowledge.

### Example

A support employee receives the question:

> "How can a customer reset their password?"

Instead of manually searching through multiple documents, the future AI-powered functionality will be able to find the relevant information and provide the appropriate answer along with the source document.

---

## 🚀 Current Features

### Authentication

The platform currently provides:

- User registration
- User login
- Bearer token authentication
- Get logged-in user
- Logout

### Knowledge Base Management

Authenticated users can manage knowledge bases:

- Create knowledge base
- View knowledge bases
- Search knowledge bases
- View a specific knowledge base
- Update knowledge base
- Delete knowledge base
- Enable/disable knowledge bases

### Document Management

Documents can be associated with a knowledge base.

Currently supported document types include:

- PDF
- DOCX
- TXT
- CSV

Available operations:

- Upload document
- List documents
- View document
- Delete document

Maximum upload size: **20 MB**

### Role-Based Access

The project includes role-based access functionality for different types of users, such as:

- Admin
- Support Agent

This allows the platform to control which users can access specific functionality.

---

## 🧠 AI/RAG Roadmap

The current project provides the backend foundation for the AI functionality.

The planned AI workflow is:

```text
Company Documents
       ↓
Document Processing
       ↓
Text Extraction
       ↓
Text Chunking
       ↓
AI Embeddings
       ↓
Vector Database
       ↓
Semantic Search
       ↓
Relevant Knowledge
       ↓
AI Response

POST    api/auth/register
POST    api/auth/login
GET     api/auth/me
POST    api/auth/logout

GET     api/knowledge-bases
POST    api/knowledge-bases
GET     api/knowledge-bases/{knowledge_base}
PUT     api/knowledge-bases/{knowledge_base}
DELETE  api/knowledge-bases/{knowledge_base}

GET     api/knowledge-bases/{knowledge_base}/documents
POST    api/knowledge-bases/{knowledge_base}/documents
GET     api/knowledge-bases/{knowledge_base}/documents/{document}
DELETE  api/knowledge-bases/{knowledge_base}/documents/{document}


PHASE 0
Local environment
        ↓
PHASE 1
Fix Laravel baseline
        ↓
PHASE 2
Authentication testing
        ↓
PHASE 3
Knowledge Base testing
        ↓
PHASE 4
Document upload testing
        ↓
PHASE 5
Python FastAPI service
        ↓
PHASE 6
Laravel → Python communication
        ↓
PHASE 7
PDF/DOCX/TXT/CSV extraction
        ↓
PHASE 8
Chunking
        ↓
PHASE 9
Store chunks in MySQL
        ↓
PHASE 10
Embeddings
        ↓
PHASE 11
Qdrant
        ↓
PHASE 12
Semantic search
        ↓
PHASE 13
RAG
        ↓
PHASE 14
LLM
        ↓
PHASE 15
Citations
        ↓
PHASE 16
Conversation API
        ↓
PHASE 17
AI Run tracking
        ↓
PHASE 18
Support Tickets
        ↓
PHASE 19
AI Ticket Suggestions
        ↓
PHASE 20
Feedback
        ↓
PHASE 21
Evaluation
        ↓
PHASE 22
Frontend
        ↓
PHASE 23
Docker
        ↓
PHASE 24
Production deployment