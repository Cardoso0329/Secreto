<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();

            // Identificação
            $table->string('app_env')->nullable();              // local/production
            $table->string('mailer')->nullable();               // smtp, ses, etc.
            $table->string('mail_type')->nullable();            // App\Mail\X
            $table->string('view')->nullable();                 // blade view (se aplicável)

            // Relações (ajusta ao teu projeto)
            $table->foreignId('recado_id')->nullable()->constrained('recados')->nullOnDelete();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Endereços
            $table->json('from')->nullable();
            $table->json('reply_to')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();

            // Conteúdo
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();               // ⚠️ pode ser grande; se não quiseres tudo, mete preview/hash
            $table->string('body_hash', 64)->nullable();         // sha256
            $table->unsignedInteger('body_size')->nullable();    // bytes

            // Estado / timing
            $table->string('status')->default('created');       // created|sending|sent|failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('attempt')->default(0);
            $table->unsignedInteger('duration_ms')->nullable();

            // Diagnóstico
            $table->string('message_id')->nullable();           // Message-ID do provider (quando existe)
            $table->unsignedInteger('smtp_code')->nullable();
            $table->text('smtp_response')->nullable();

            $table->text('error_message')->nullable();
            $table->longText('error_trace')->nullable();

            // Auditoria (web)
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('trace_id')->nullable();             // para correlacionar com logs do laravel

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['recado_id', 'created_at']);
            $table->index(['mail_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
