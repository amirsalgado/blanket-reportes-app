<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('cliente')->after('remember_token');
            $table->string('company')->nullable()->after('email');
            $table->string('client_type')->nullable()->after('role');
            $table->string('nit')->nullable()->unique()->after('company');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'company', 'client_type', 'nit']);
        });
    }
};
