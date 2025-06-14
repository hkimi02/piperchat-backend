<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['organisation', 'project', 'private'])->default('organisation');
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatrooms');
    }
};
