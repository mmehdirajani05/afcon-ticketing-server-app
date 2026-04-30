<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Email templates have been moved from the database to file-based Blade views
 * (resources/views/emails/). The email_templates table is no longer needed.
 *
 * dropIfExists() makes this safe on any server state:
 *   - Existing server: drops the table.
 *   - Fresh server:    table never existed, silently does nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('email_templates');
    }

    public function down(): void
    {
        // Intentionally not re-creating — email templates now live in Blade views.
        // If a rollback is truly needed, restore from git history.
    }
};
