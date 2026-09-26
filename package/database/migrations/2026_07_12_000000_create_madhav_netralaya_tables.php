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
        // 1. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('password');
            $table->string('role')->default('reception');
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Departments Table
        Schema::create('departments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('overview');
            $table->json('symptoms')->nullable();
            $table->json('diagnosis')->nullable();
            $table->json('treatments')->nullable();
            $table->json('technology')->nullable();
            $table->json('faqs')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        // 3. Doctors Table
        Schema::create('doctors', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('qualification');
            $table->integer('experience');
            $table->string('specialty');
            $table->string('department_id');
            $table->text('biography')->nullable();
            $table->json('languages')->nullable();
            $table->string('photo')->nullable();
            $table->json('consultation_timing')->nullable();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });

        // 4. Diagnostics Table
        Schema::create('diagnostics', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->json('indications')->nullable();
            $table->text('procedure')->nullable();
            $table->json('benefits')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // 5. Facilities Table
        Schema::create('facilities', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // 6. Blog Categories Table
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 7. Blogs Table
        Schema::create('blogs', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('category_id');
            $table->json('tags')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('author');
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('draft');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('blog_categories')->onDelete('cascade');
        });

        // 8. Events Table
        Schema::create('events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description');
            $table->date('date');
            $table->string('time');
            $table->string('location');
            $table->string('image')->nullable();
            $table->string('status')->default('upcoming');
            $table->json('gallery')->nullable();
            $table->integer('registrations_count')->default(0);
            $table->timestamps();
        });

        // 9. Event Registrations Table
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('event_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->timestamp('registered_at');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });

        // 10. Testimonials Table
        Schema::create('testimonials', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('patient_name');
            $table->integer('age');
            $table->string('treatment');
            $table->integer('rating')->default(5);
            $table->text('comment');
            $table->string('photo')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamps();
        });

        // 11. Gallery Items Table
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('category');
            $table->string('image_url');
            $table->timestamps();
        });

        // 12. Notices Table
        Schema::create('notices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('content');
            $table->string('category');
            $table->date('date');
            $table->boolean('important')->default(false);
            $table->timestamps();
        });

        // 13. Appointments Table
        Schema::create('appointments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('patient_name');
            $table->integer('patient_age');
            $table->string('patient_gender');
            $table->string('patient_phone');
            $table->string('patient_email');
            $table->string('department_id');
            $table->string('doctor_id');
            $table->date('date');
            $table->string('time_slot');
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });

        // 14. Enquiries Table
        Schema::create('enquiries', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('unread');
            $table->timestamps();
        });

        // 15. Subscribers Table
        Schema::create('subscribers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('email')->unique();
            $table->timestamp('subscribed_at');
            $table->timestamps();
        });

        // 16. Sliders Table
        Schema::create('sliders', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('background_image');
            $table->string('cta_text')->nullable();
            $table->string('cta_link')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 17. Settings Table
        Schema::create('settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('logo');
            $table->string('favicon');
            $table->string('hospital_name');
            $table->string('tagline')->nullable();
            $table->string('primary_color')->default('#2563eb');
            $table->string('secondary_color')->default('#0d9488');
            $table->text('address');
            $table->json('phone_numbers');
            $table->json('emails');
            $table->json('working_hours');
            $table->json('emergency_contacts');
            $table->text('google_map_embed_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->json('social_media')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('google_search_console_verification')->nullable();
            $table->json('email_smtp')->nullable();
            $table->timestamps();
        });

        // 18. SEO Meta Table
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('page_key')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_type')->default('website');
            $table->string('og_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('sliders');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('enquiries');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('gallery_items');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('facilities');
        Schema::dropIfExists('diagnostics');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('users');
    }
};
