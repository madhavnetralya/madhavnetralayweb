<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class FaqSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faq:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely seed initial FAQ categories and questions into the JSON state store.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Reading current state from database...");
        
        $row = DB::table('state_store')->where('key', 'state')->first();
        if (!$row) {
            $this->error("State store not found!");
            return Command::FAILURE;
        }

        $stateStr = $row->value;
        
        // Backup
        $backupPath = storage_path('app/state_backup_before_faq_' . date('Ymd_His') . '.json');
        File::put($backupPath, $stateStr);
        $this->info("Backup saved to: " . $backupPath);

        $state = json_decode($stateStr, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Failed to decode JSON state: " . json_last_error_msg());
            return Command::FAILURE;
        }

        // Check if FAQs already exist to ensure idempotency
        // (Removed abort logic here so we can also check/add custom page below)
        
        $this->info("Injecting default FAQ categories and questions...");

        $catGeneral = 'faq_cat_general';
        $catCataract = 'faq_cat_cataract';
        $catTreatments = 'faq_cat_treatments';
        $catEyeDonation = 'faq_cat_eyedonation';
        $catAppointments = 'faq_cat_appointments';

        $faqCategories = [
            ['id' => $catGeneral, 'name' => 'General Eye Care', 'slug' => 'general-eye-care', 'display_order' => 1, 'is_active' => true],
            ['id' => $catCataract, 'name' => 'Cataract', 'slug' => 'cataract', 'display_order' => 2, 'is_active' => true],
            ['id' => $catTreatments, 'name' => 'Treatments & Procedures', 'slug' => 'treatments', 'display_order' => 3, 'is_active' => true],
            ['id' => $catEyeDonation, 'name' => 'Eye Donation', 'slug' => 'eye-donation', 'display_order' => 4, 'is_active' => true],
            ['id' => $catAppointments, 'name' => 'Appointments', 'slug' => 'appointments', 'display_order' => 5, 'is_active' => true],
        ];

        $faqs = [
            // General
            ['id' => 'faq_1', 'category_id' => $catGeneral, 'question' => 'How often should I have an eye examination?', 'answer' => '<p>For most adults, a comprehensive eye exam is recommended every 1-2 years. However, if you have diabetes, high blood pressure, or a family history of eye disease, you may need more frequent check-ups.</p>', 'display_order' => 1, 'is_published' => true, 'is_featured' => true],
            ['id' => 'faq_2', 'category_id' => $catGeneral, 'question' => 'What are common symptoms that require an eye examination?', 'answer' => '<p>You should schedule an eye exam if you experience blurred vision, frequent headaches, eye pain, redness, floaters, flashes of light, or sudden changes in your vision.</p>', 'display_order' => 2, 'is_published' => true, 'is_featured' => false],
            ['id' => 'faq_3', 'category_id' => $catGeneral, 'question' => 'Can diabetes affect eyesight?', 'answer' => '<p>Yes, diabetes can lead to a condition called diabetic retinopathy, which damages the blood vessels in the retina. Annual eye exams are crucial for diabetic patients to prevent vision loss.</p>', 'display_order' => 3, 'is_published' => true, 'is_featured' => false],

            // Cataract
            ['id' => 'faq_4', 'category_id' => $catCataract, 'question' => 'What is a cataract?', 'answer' => '<p>A cataract is a clouding of the normally clear lens of the eye. It is like looking through a frosty or fogged-up window and is a common part of aging.</p>', 'display_order' => 1, 'is_published' => true, 'is_featured' => true],
            ['id' => 'faq_5', 'category_id' => $catCataract, 'question' => 'How do I know if I need cataract surgery?', 'answer' => '<p>Surgery is typically recommended when cataracts begin to interfere with your daily activities, such as reading, driving, or watching television. Your ophthalmologist will help you decide the right time.</p>', 'display_order' => 2, 'is_published' => true, 'is_featured' => false],
            ['id' => 'faq_6', 'category_id' => $catCataract, 'question' => 'How long does cataract surgery take and what is the recovery time?', 'answer' => '<p>The surgery itself usually takes about 15-30 minutes. Recovery is generally fast; most patients notice improved vision within a few days, though complete healing can take a few weeks.</p>', 'display_order' => 3, 'is_published' => true, 'is_featured' => false],

            // Treatments & Procedures
            ['id' => 'faq_7', 'category_id' => $catTreatments, 'question' => 'What is LASIK surgery?', 'answer' => '<p>LASIK is a popular refractive surgery that reshapes the cornea using a laser to correct nearsightedness, farsightedness, and astigmatism, often eliminating the need for glasses or contact lenses.</p>', 'display_order' => 1, 'is_published' => true, 'is_featured' => false],
            ['id' => 'faq_8', 'category_id' => $catTreatments, 'question' => 'What is glaucoma and how is it treated?', 'answer' => '<p>Glaucoma is a group of eye conditions that damage the optic nerve, often due to high eye pressure. It can be treated with prescription eye drops, laser therapy, or surgery to lower the pressure.</p>', 'display_order' => 2, 'is_published' => true, 'is_featured' => false],
            ['id' => 'faq_9', 'category_id' => $catTreatments, 'question' => 'Does every eye condition require surgery?', 'answer' => '<p>No, many eye conditions can be effectively managed with prescription glasses, medications, or lifestyle changes. Surgery is recommended only when non-invasive treatments are insufficient.</p>', 'display_order' => 3, 'is_published' => true, 'is_featured' => false],

            // Eye Donation
            ['id' => 'faq_10', 'category_id' => $catEyeDonation, 'question' => 'Who can donate eyes?', 'answer' => '<p>Anyone of any age, sex, or blood group can donate their eyes. Even individuals who wear glasses, have cataracts, or suffer from diabetes or hypertension can be donors.</p>', 'display_order' => 1, 'is_published' => true, 'is_featured' => true],
            ['id' => 'faq_11', 'category_id' => $catEyeDonation, 'question' => 'How soon after death should eye donation be performed?', 'answer' => '<p>Eye donation must ideally take place within 4 to 6 hours after death. It is important for relatives to contact the nearest eye bank as soon as possible.</p>', 'display_order' => 2, 'is_published' => true, 'is_featured' => false],
            ['id' => 'faq_12', 'category_id' => $catEyeDonation, 'question' => 'How can I register for eye donation?', 'answer' => '<p>You can fill out our online Noble Eye Donor Pledge form on our website. You will receive a digital donor certification card upon completion.</p>', 'display_order' => 3, 'is_published' => true, 'is_featured' => false],

            // Appointments
            ['id' => 'faq_13', 'category_id' => $catAppointments, 'question' => 'How can I book an appointment?', 'answer' => '<p>You can book an appointment online through our website, by calling our reception desk, or by walking into our hospital directly.</p>', 'display_order' => 1, 'is_published' => true, 'is_featured' => true],
            ['id' => 'faq_14', 'category_id' => $catAppointments, 'question' => 'What documents should I bring for my first consultation?', 'answer' => '<p>Please bring a valid photo ID, any previous medical and eye records, a list of your current medications, and your insurance or TPA card if applicable.</p>', 'display_order' => 2, 'is_published' => true, 'is_featured' => false],
            ['id' => 'faq_15', 'category_id' => $catAppointments, 'question' => 'Does Madhav Netralaya accept health insurance?', 'answer' => '<p>Yes, we are empanelled with major government schemes, corporate healthcare programs, and insurance TPAs for cashless treatments and surgeries.</p>', 'display_order' => 3, 'is_published' => true, 'is_featured' => false],
        ];

        // Merge safely
        if (!isset($state['faqCategories'])) {
            $state['faqCategories'] = $faqCategories;
        }
        if (!isset($state['faqs'])) {
            $state['faqs'] = $faqs;
        }

        // Add FAQ to customPages so it appears in the dynamic top menu
        if (!isset($state['customPages'])) {
            $state['customPages'] = [];
        }
        
        $hasFaqPage = false;
        foreach ($state['customPages'] as $cp) {
            if ($cp['id'] === 'faq' || $cp['slug'] === 'faq') {
                $hasFaqPage = true;
                break;
            }
        }
        
        if (!$hasFaqPage) {
            $state['customPages'][] = [
                'id' => 'faq',
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '', // Managed by dedicated FAQ component
                'order' => 99,
                'level' => 0,
                'hideFromMenu' => false,
                'metaTitle' => 'Frequently Asked Questions | Madhav Netralaya',
                'metaDescription' => 'Find answers to frequently asked questions about eye care, eye diseases, treatments, surgeries, appointments, eye donation and services at Madhav Netralaya.',
            ];
            $this->info("Injected FAQ custom page for navigation.");
        }

        $newStateStr = json_encode($state);
        
        DB::table('state_store')->where('key', 'state')->update(['value' => $newStateStr]);

        $this->info("Successfully seeded FAQ categories and questions.");
        return Command::SUCCESS;
    }
}
