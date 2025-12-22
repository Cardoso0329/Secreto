<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('recados', function (Blueprint $table) {
        $table->id();

        // Dados do cliente e operador
        $table->string('name');
        $table->string('contact_client');
        $table->string('plate')->nullable();
        $table->string('operator_email')->nullable();
        $table->string('wip')->nullable(); // Nullable porque o formulário Central não tem
        // Ligações a outras tabelas        // Ligações a outras tabelas
        $table->foreignId('sla_id')->constrained('slas')->onDelete('cascade');
        $table->foreignId('tipo_id')->constrained('tipos')->onDelete('cascade');
        $table->foreignId('origem_id')->constrained('origens')->onDelete('cascade');
        $table->foreignId('setor_id')->constrained('setores')->onDelete('cascade');
        $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');
        $table->foreignId('destinatario_id')->nullable()->constrained('destinatarios')->onDelete('cascade');
        $table->string('destinatario_livre')->nullable();
$table->foreignId('campanha_id')->nullable()->constrained('campanhas')->onDelete('set null');



        $table->longText('mensagem');
        $table->string('ficheiro')->nullable(); // upload de ficheiros

        $table->integer('aviso_nivel')->default(1); // 1º, 2º, 3º aviso...
        
        $table->foreignId('aviso_id')->nullable()->constrained('avisos')->onDelete('set null');

        $table->foreignId('estado_id')->constrained('estados')->onDelete('cascade');
        $table->text('observacoes')->nullable();
        $table->timestamp('abertura')->nullable();
        $table->timestamp('termino')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recados');
    }
};
