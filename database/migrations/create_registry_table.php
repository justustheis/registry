<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('registries', function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->nullableMorphs('registrable');
            $table->longText('value')->nullable();
            $table->string('type')->nullable()->index();
            $table->boolean('encrypted')->default(false)->index();
            $table->foreignId('updated_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['key', 'registrable_type', 'registrable_id']);
            $table->index(['key', 'registrable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registries');
    }
};
