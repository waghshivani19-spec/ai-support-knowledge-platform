<?php

namespace App\Services;

use App\Jobs\ProcessKnowledgeDocument;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeDocumentService
{
    public function upload(
        User $user,
        KnowledgeBase $knowledgeBase,
        UploadedFile $file,
        ?string $title = null
    ): KnowledgeDocument {

        $hash = hash_file(
            'sha256',
            $file->getRealPath()
        );

        $existingDocument = KnowledgeDocument::where(
            'knowledge_base_id',
            $knowledgeBase->id
        )
            ->where(
                'file_hash',
                $hash
            )
            ->first();

        if ($existingDocument) {
            return $existingDocument;
        }

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $filename = Str::uuid()
            . '.' . $extension;

        $directory = sprintf(
            'knowledge-bases/%d/documents',
            $knowledgeBase->id
        );

        $path = $file->storeAs(
            $directory,
            $filename,
            'local'
        );

        $document = KnowledgeDocument::create([
            'knowledge_base_id' =>
                $knowledgeBase->id,

            'uploaded_by' =>
                $user->id,

            'title' =>
                $title
                ?? pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                ),

            'original_filename' =>
                $file->getClientOriginalName(),

            'file_path' => $path,

            'mime_type' =>
                $file->getMimeType(),

            'file_size' =>
                $file->getSize(),

            'file_hash' =>
                $hash,

            'source_type' =>
                'upload',

            'status' =>
                'pending',
        ]);

        ProcessKnowledgeDocument::dispatch(
            $document->id
        );

        return $document;
    }

    public function delete(
        KnowledgeDocument $document
    ): void {
        if (
            $document->file_path &&
            Storage::disk('local')->exists(
                $document->file_path
            )
        ) {
            Storage::disk('local')->delete(
                $document->file_path
            );
        }

        $document->delete();
    }
}