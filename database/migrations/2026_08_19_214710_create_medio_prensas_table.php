<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medios_prensa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('territorio_id')->nullable()->constrained('territorios')->onDelete('set null');
            $table->string('nombre');
            $table->string('tipo_medio')->default('digital'); // 'digital', 'impreso', 'radio', 'tv'
            $table->string('url_sitio')->nullable();
            $table->string('alcance_tipo')->default('provincial'); // 'local', 'provincial', 'nacional'
            $table->string('sesgo_editorial_estimado')->default('independiente'); // 'oficialista', 'opositor', 'independiente'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medios_prensa');
    }
};
