<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreorderSheetsTable extends Migration
{
    public function up(): void
    {
        Schema::create('preorder_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('sheet_title')->nullable();
            $table->string('title');
            $table->integer('start_at_row')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preorder_sheets');
    }
}
