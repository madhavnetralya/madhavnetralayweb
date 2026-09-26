<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Diagnostic;
use App\Models\Facility;
use App\Models\BlogCategory;
use App\Models\Blog;
use App\Models\Event;
use App\Models\Testimonial;
use App\Models\GalleryItem;
use App\Models\Notice;
use App\Models\Slider;
use App\Models\Setting;
use App\Models\SeoMeta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        User::create([
            'id' => 'u1',
            'username' => 'admin',
            'email' => 'pranav.dhomne@gmail.com',
            'name' => 'Dr. Pranav Dhomne',
            'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD', \Illuminate\Support\Str::random(16))),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        User::create([
            'id' => 'u2',
            'username' => 'receptionist',
            'email' => 'reception@madhavnetralaya.org',
            'name' => 'Sneha Sharma',
            'password' => Hash::make(env('INITIAL_STAFF_PASSWORD', \Illuminate\Support\Str::random(16))),
            'role' => 'reception',
            'status' => 'active',
        ]);

        // 2. Departments
        Department::create([
            'id' => 'cataract',
            'name' => 'Cataract & Lens Implant',
            'overview' => "A cataract is a clouding of the eye's natural lens, which lies behind the iris and the pupil. It is the most common cause of vision loss in people over age 40. At Madhav Netralaya, we offer micro-incision cataract surgery (MICS) with premium foldable lens implants (Monofocal, Multifocal, and Toric) for crisp and glass-free vision.",
            'symptoms' => ["Cloudy or blurry vision", "Difficulty with night vision", "Sensitivity to light and glare", "Seeing 'halos' around lights", "Frequent changes in eyeglass prescription"],
            'diagnosis' => ["Visual acuity test", "Slit-lamp examination", "Retinal examination", "Optical Biometry (IOL Master)"],
            'treatments' => ["Micro-Incision Cataract Surgery (MICS)", "Phacoemulsification", "Femtosecond Laser Assisted Cataract Surgery (FLACS)", "Premium IOL Implantation (Toric, Multifocal, Trifocal)"],
            'technology' => ["Alcon Centurion Vision System", "ZEISS Lumera 700 Microscope", "IOL Master 700"],
            'faqs' => [
                ["question" => "Is cataract surgery painful?", "answer" => "No, the procedure is virtually painless. We use anesthetic eye drops to numb your eye before the surgery."],
                ["question" => "How long does the recovery take?", "answer" => "Most patients notice a significant improvement in vision within 24 to 48 hours. Complete healing takes about 3 to 4 weeks."]
            ],
            'banner_image' => "https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80",
            'meta_title' => "Advanced Cataract Surgery & MICS in Nagpur - Madhav Netralaya",
            'meta_description' => "Restore crystal clear vision with state-of-the-art micro-incision cataract surgery at Nagpur's premier eye care institute. Premium Multifocal and Toric IOLs."
        ]);

        Department::create([
            'id' => 'retina',
            'name' => 'Retina & Vitreous',
            'overview' => "The retina is the light-sensitive tissue lining the back of our eye. Retinal disorders can affect vital vision and are often associated with systemic conditions like diabetes and hypertension. Our advanced Retina clinic handles Diabetic Retinopathy, Macular Degeneration (ARMD), Retinal Detachments, and Retinal Vein Occlusions using state-of-the-art laser and surgical treatments.",
            'symptoms' => ["Sudden increase in floaters or flashes of light", "A shadow or curtain over your visual field", "Wavy or distorted central vision", "Sudden, painless loss of vision"],
            'diagnosis' => ["Fundus Fluorescein Angiography (FFA)", "Optical Coherence Tomography (OCT)", "B-Scan Ultrasound"],
            'treatments' => ["Intravitreal Anti-VEGF Injections", "Retinal Laser Photocoagulation", "Micro-incision Vitrectomy Surgery (MIVS) for Retinal Detachment"],
            'technology' => ["Heidelberg Spectralis OCT", "Constellation Vitrectomy Suite", "ZEISS Visulas Green Laser"],
            'faqs' => [
                ["question" => "What is Diabetic Retinopathy?", "answer" => "It is a diabetes complication that affects eyes. It's caused by damage to the blood vessels of the light-sensitive tissue at the back of the eye (retina)."],
                ["question" => "Can a detached retina be cured?", "answer" => "Yes, a detached retina is a medical emergency that almost always requires surgery to reattach the retina to its normal position."]
            ],
            'banner_image' => "https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1200&q=80",
            'meta_title' => "Retina Specialist Nagpur - Diabetic Retinopathy Treatment",
            'meta_description' => "Comprehensive Vitreoretinal care. Expert diagnosis and surgery for retinal detachment, diabetic retinopathy, and macular degeneration at Nagpur."
        ]);

        Department::create([
            'id' => 'cornea',
            'name' => 'Cornea & Refractive (LASIK)',
            'overview' => "The cornea is the eye's outermost layer. Cornea team at Madhav Netralaya treats corneal infections, dry eyes, keratoconus, and performs corneal transplantations (Keratoplasty). We also offer state-of-the-art refractive surgery (LASIK, Trans-PRK, ICL) to help you get rid of spectacles permanently.",
            'symptoms' => ["Severe eye pain or burning", "Redness and excessive tearing", "Extreme light sensitivity", "Gradual decrease in vision", "Frequent changes in eye spectacle number (Keratoconus)"],
            'diagnosis' => ["Corneal Topography (Pentacam)", "Specular Microscopy", "Pachymetry (Corneal thickness measurement)"],
            'treatments' => ["Full/Partial Thickness Corneal Transplants (PKP, DALK, DSEK)", "Corneal Collagen Cross-linking (C3R) for Keratoconus", "Bladeless Custom LASIK & ICL Implantation"],
            'technology' => ["Pentacam AXL Wave", "Wavelight EX500 Excimer Laser"],
            'faqs' => [
                ["question" => "Am I a candidate for LASIK?", "answer" => "Generally, candidates must be at least 18 years old, have a stable glass prescription for at least a year, and have healthy corneas with adequate thickness."],
                ["question" => "Where do donor corneas come from?", "answer" => "Donor corneas come from deceased individuals who graciously donated their eyes. Madhav Netralaya partners with major eye banks to source corneas ethically."]
            ],
            'banner_image' => "https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=1200&q=80",
            'meta_title' => "LASIK & Cornea Treatment in Nagpur - Madhav Netralaya",
            'meta_description' => "Experience blade-free visual freedom. Top Cornea specialists and custom LASIK treatments in Nagpur. Contact us for specular microscopy and transplants."
        ]);

        Department::create([
            'id' => 'glaucoma',
            'name' => 'Glaucoma Services',
            'overview' => "Known as the 'Silent Thief of Sight', Glaucoma is a group of eye conditions that damage the optic nerve, often linked to abnormally high pressure in your eye. It is irreversible but manageable. Early detection at our advanced glaucoma clinic is crucial for preserving your visual field.",
            'symptoms' => ["Gradual loss of peripheral (side) vision", "Tunnel vision in advanced stages", "Severe eye pain with headache (Acute Glaucoma)", "Haloes around lights"],
            'diagnosis' => ["Applanation Tonometry (IOP Check)", "Gonioscopy (Angle evaluation)", "Humphrey Visual Field (HVF) Analysis", "Optic Nerve OCT"],
            'treatments' => ["Prescription Eye Drops", "Selective Laser Trabeculoplasty (SLT)", "Trabeculectomy Surgery", "Glaucoma Valve/Shunt Implants"],
            'technology' => ["Humphrey Field Analyzer 3", "Nidek YAG/SLT Laser System"],
            'faqs' => [
                ["question" => "Is vision lost from glaucoma restorable?", "answer" => "No, vision lost due to glaucoma is permanent. However, with appropriate treatments, further vision loss can be successfully halted."],
                ["question" => "How often should I be screened for Glaucoma?", "answer" => "If you are over 40 or have a family history, an annual screening is highly recommended."]
            ],
            'banner_image' => "https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1200&q=80",
            'meta_title' => "Glaucoma Treatment & Screening Nagpur - Madhav Netralaya",
            'meta_description' => "Save your sight from glaucoma. Get diagnosed early using Humphrey Visual Fields & OCT at Madhav Netralaya Eye Institute, Nagpur."
        ]);

        Department::create([
            'id' => 'pediatric',
            'name' => 'Pediatric & Squint Clinic',
            'overview' => "Children require specialized care tailored to their growing eyes. Pediatric division manages childhood cataracts, congenital glaucoma, squint (strabismus), lazy eyes (amblyopia), and complex pediatric eye disorders in a child-friendly, playful environment.",
            'symptoms' => ["Squinting or misalignment of eyes", "Frequent rubbing of eyes", "Holding books/devices very close", "White reflex in the pupil"],
            'diagnosis' => ["Cycloplegic Refraction", "Orthoptic evaluation", "Stereopsis (3D vision) test"],
            'treatments' => ["Custom Eyeglasses & Patching therapy for Amblyopia", "Squint correction surgery (Strabismus)", "Pediatric Cataract Surgery with specialized IOLs"],
            'technology' => ["Plusoptix Pediatric Refractometer", "Synoptophore for squint evaluation"],
            'faqs' => [
                ["question" => "At what age should a child have their first eye test?", "answer" => "Every child should undergo a baseline eye exam by age 3, or sooner if you observe eye deviation or difficulty focusing."],
                ["question" => "Can squint be corrected without surgery?", "answer" => "Some squints can be fully corrected with glasses or exercises. If these options are insufficient, safe muscle adjustment surgery is performed."]
            ],
            'banner_image' => "https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?auto=format&fit=crop&w=1200&q=80",
            'meta_title' => "Pediatric Eye Specialist & Squint Surgery in Nagpur",
            'meta_description' => "Expert eye care for infants, children & teenagers. Advanced squint and lazy eye treatments by dedicated pediatric ophthalmologists."
        ]);

        Department::create([
            'id' => 'oculoplasty',
            'name' => 'Oculoplasty & Aesthetics',
            'overview' => "Oculoplastic surgery refers to specialized cosmetic and reconstructive procedures of the structures around the eye: eyelids, orbit (eye socket), and tear ducts. We treat droopy eyelids, watery eyes, orbital fractures, and offer anti-aging cosmetic treatments.",
            'symptoms' => ["Droopy eyelids (Ptosis)", "Eyelids turning inward or outward", "Constant watery eyes", "Bulging eyes (Thyroid eye disease)"],
            'diagnosis' => ["Hertel Exophthalmometry", "Syrgining and dacryocystography"],
            'treatments' => ["Ptosis correction surgery", "Dacryocystorhinostomy (DCR) for watery eyes", "Eyelid tumor reconstruction", "Botox and cosmetic fillers"],
            'technology' => ["State-of-the-art radiofrequency cautery", "Endoscopic DCR system"],
            'faqs' => [
                ["question" => "Will oculoplastic surgery leave visible scars?", "answer" => "Our specialists utilize natural eyelid creases and minimally invasive approaches to make any incision lines practically invisible after healing."]
            ],
            'banner_image' => "https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=1200&q=80",
            'meta_title' => "Oculoplasty & Eyelid Surgery Nagpur - Madhav Netralaya",
            'meta_description' => "Advanced reconstructive and cosmetic eyelid surgeries. Treatment for droopy eyelids, tear duct obstructions (DCR), and aesthetic enhancements."
        ]);

        // 3. Doctors
        Doctor::create([
            'id' => 'doc1',
            'name' => 'Dr. Madhav S. Dhomne',
            'qualification' => 'MBBS, MS (Ophthalmology), FMRF (Sankara Nethralaya)',
            'experience' => 25,
            'specialty' => 'Vitreoretinal Surgery & Advanced Cataract',
            'department_id' => 'retina',
            'biography' => 'Dr. Madhav S. Dhomne is a pioneer in Vitreoretinal surgery with over 25 years of extensive experience. He completed his fellowship from the prestigious Sankara Nethralaya, Chennai, and has performed over 15,000 successful complex ocular surgeries.',
            'languages' => ['English', 'Hindi', 'Marathi'],
            'photo' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=600&q=80',
            'consultation_timing' => [
                'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                'time' => '10:00 AM - 01:00 PM, 04:00 PM - 07:00 PM'
            ]
        ]);

        Doctor::create([
            'id' => 'doc2',
            'name' => 'Dr. Ananya Iyer',
            'qualification' => 'MBBS, MD, DNB (Ophthalmology), Cornea Fellowship (LV Prasad)',
            'experience' => 12,
            'specialty' => 'Cornea & Refractive (LASIK) Specialist',
            'department_id' => 'cornea',
            'biography' => 'Dr. Ananya Iyer is an expert in Corneal transplants (DALK, DSEK) and advanced LASIK/SMILE refractive procedures. She is dedicated to curing corneal blindness and has published multiple papers in international journals.',
            'languages' => ['English', 'Hindi', 'Tamil'],
            'photo' => 'https://images.unsplash.com/photo-1594824813573-246434de83fb?auto=format&fit=crop&w=600&q=80',
            'consultation_timing' => [
                'days' => ['Mon', 'Wed', 'Fri'],
                'time' => '11:00 AM - 04:00 PM'
            ]
        ]);

        Doctor::create([
            'id' => 'doc3',
            'name' => 'Dr. Rajesh K. Patel',
            'qualification' => 'MBBS, MS, Fellowship in Glaucoma (Aravind Eye Hospital)',
            'experience' => 15,
            'specialty' => 'Glaucoma & Microincision Cataract',
            'department_id' => 'glaucoma',
            'biography' => 'Dr. Rajesh Patel specialized in early detection and advanced laser/surgical management of Glaucoma. He has a patient-first philosophy and uses state-of-the-art diagnostic testing to preserve vision.',
            'languages' => ['English', 'Hindi', 'Gujarati'],
            'photo' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=600&q=80',
            'consultation_timing' => [
                'days' => ['Tue', 'Thu', 'Sat'],
                'time' => '09:00 AM - 01:00 PM'
            ]
        ]);

        Doctor::create([
            'id' => 'doc4',
            'name' => 'Dr. Meera Deshmukh',
            'qualification' => 'MBBS, MS, Pediatric Ophthalmology Fellowship',
            'experience' => 10,
            'specialty' => 'Pediatric Ophthalmology & Squint',
            'department_id' => 'pediatric',
            'biography' => "Dr. Meera Deshmukh is passionate about children's eye health. She specializes in pediatric cataracts, lazy eye (amblyopia), and complex squint corrections in both children and adults.",
            'languages' => ['English', 'Marathi', 'Hindi'],
            'photo' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=600&q=80',
            'consultation_timing' => [
                'days' => ['Mon', 'Tue', 'Thu', 'Fri'],
                'time' => '02:00 PM - 06:00 PM'
            ]
        ]);

        // 4. Diagnostics
        Diagnostic::create([
            'id' => 'oct',
            'name' => 'Optical Coherence Tomography (OCT)',
            'description' => "OCT is a non-invasive imaging test that uses light waves to take cross-section pictures of your retina. This allows our ophthalmologists to see each of the retina's distinctive layers, mapping and measuring their thickness.",
            'indications' => ["Age-related macular degeneration (ARMD)", "Diabetic retinopathy", "Macular hole & epiretinal membrane", "Glaucoma optic nerve mapping"],
            'procedure' => "You sit in front of the OCT machine and rest your chin. Light passes into your eye without touching it. The scan completes in under 2 minutes per eye.",
            'benefits' => ["Extremely precise micro-level details", "Completely painless and touchless", "No radiation exposure"],
            'image' => "https://images.unsplash.com/photo-1579684389782-64d84b5e901a?auto=format&fit=crop&w=600&q=80"
        ]);

        Diagnostic::create([
            'id' => 'fundus',
            'name' => 'Fundus Photography & Angiography',
            'description' => "Fundus photography uses a specialized microscope coupled with a camera to photograph the interior surface of the eye, capturing the retina, optic disc, macula, and posterior pole.",
            'indications' => ["Monitoring retinal disease progression", "Hypertensive retinopathy evaluation", "Diabetic macular edema tracking"],
            'procedure' => "Pupils are usually dilated with special drops. You look at a target light while bright, brief flashes capture high-definition photographs.",
            'benefits' => ["Permanent documentation of eye status", "Easier comparison during follow-up visits", "Excellent clarity of blood vessels"],
            'image' => "https://images.unsplash.com/photo-1551076805-e1869033e561?auto=format&fit=crop&w=600&q=80"
        ]);

        Diagnostic::create([
            'id' => 'hvf',
            'name' => 'Humphrey Visual Field Analysis',
            'description' => "Visual Field analysis measures your peripheral and central vision. It maps the blind spots in your visual pathway, typically caused by glaucoma or neurological disorders.",
            'indications' => ["Glaucoma diagnosis & progression tracking", "Optic nerve damage", "Brain tumors or strokes affecting vision"],
            'procedure' => "You look into a bowl-shaped instrument. Whenever you see a tiny light flash in your side vision, you press a hand-held buzzer button.",
            'benefits' => ["Early detection of subtle vision loss", "Quantitative tracking of treatment success", "Highly standardized world benchmark"],
            'image' => "https://images.unsplash.com/photo-1511174511562-5f7f18b874f8?auto=format&fit=crop&w=600&q=80"
        ]);

        // 5. Facilities
        Facility::create([
            'id' => 'pharmacy',
            'name' => 'In-House 24/7 Pharmacy',
            'description' => 'Get all specialized ophthalmic eye drops, medicines, and surgical consumables right inside the hospital campus, ensuring genuine drugs at subsidized rates.',
            'image' => 'https://images.unsplash.com/photo-1586015555751-63bb77f4322a?auto=format&fit=crop&w=600&q=80',
            'icon' => 'Pill'
        ]);

        Facility::create([
            'id' => 'optical',
            'name' => 'Madhav Opticals & Vision Lounge',
            'description' => 'A premium optical wing offering scientifically verified lenses, custom frames, contact lenses, and low-vision aids fitted with computerized optical measurements.',
            'image' => 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?auto=format&fit=crop&w=600&q=80',
            'icon' => 'Glasses'
        ]);

        Facility::create([
            'id' => 'eyebank',
            'name' => 'Rotary Madhav Eye Bank',
            'description' => 'A registered, state-of-the-art eye banking facility facilitating noble eye donations, 24/7 harvest teams, and advanced corneal preservation technologies.',
            'image' => 'https://images.unsplash.com/photo-1530026405186-ed1ea0ac7a63?auto=format&fit=crop&w=600&q=80',
            'icon' => 'Heart'
        ]);

        Facility::create([
            'id' => 'ot',
            'name' => 'Ultra-Modern Laminar Flow OTs',
            'description' => 'Modular Operation Theatres with Hepa-filters and laminar airflow to maintain 100% sterile, infection-free surgical environments for micro-surgeries.',
            'image' => 'https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=600&q=80',
            'icon' => 'Activity'
        ]);

        // 6. Blog Categories
        BlogCategory::create([
            'id' => 'cat1',
            'name' => 'Daily Wellness',
            'slug' => 'wellness'
        ]);

        BlogCategory::create([
            'id' => 'cat2',
            'name' => 'Clinical Insights',
            'slug' => 'clinical-insights'
        ]);

        // 7. Blogs
        Blog::create([
            'id' => 'b1',
            'title' => '5 Crucial Tips to Prevent Computer Vision Syndrome in 2026',
            'slug' => 'prevent-computer-vision-syndrome',
            'content' => "<p>With screens dominating our workspaces and remote routines, digital eye strain or Computer Vision Syndrome (CVS) has become extremely common.</p><h4>1. Use the 20-20-20 Rule</h4><p>Every 20 minutes, take a break to look at an object at least 20 feet away for 20 seconds. This relaxes the focusing muscle inside your eyes.</p><h4>2. Optimize Your Workstation</h4><p>Ensure your screen is at arm's length (about 20-24 inches) and the top of the monitor is at or slightly below eye level. This minimizes strain on your neck and eye muscles.</p><h4>3. Blink Frequently</h4><p>When working on a screen, our blink rate drops by 50%. Consciously make an effort to blink more to keep your corneal surface lubricated and prevent severe dry eyes.</p><h4>4. Wear Blue-Cut Anti-Reflective Lenses</h4><p>Computer glasses can absorb harmful blue-violet light and reduce annoying reflections. Speak to our Madhav Opticals specialists for custom lenses.</p><h4>5. Book a Comprehensive Eye Test</h4><p>Often, undetected minor power corrections are the major source of headache and fatigue. Get your eyes screened once a year!</p>",
            'category_id' => 'cat1',
            'tags' => ['Eye Care', 'Digital Strain', 'Healthy Eyes'],
            'featured_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
            'author' => 'Dr. Madhav S. Dhomne',
            'published_at' => '2026-06-15 10:00:00',
            'status' => 'published',
            'meta_title' => 'How to Prevent Computer Vision Syndrome - Madhav Netralaya',
            'meta_description' => 'Learn professional tips to reduce digital eye strain, optimize workstation ergonomics, and protect your vision during screen work.'
        ]);

        Blog::create([
            'id' => 'b2',
            'title' => 'Understanding Glaucoma: The Silent Thief of Vision',
            'slug' => 'understanding-glaucoma-silent-thief',
            'content' => "<p>Glaucoma is often called the silent thief of sight because most forms of this condition produce no early symptoms, pain, or warnings.</p><h4>Why is it dangerous?</h4><p>As the pressure inside the eye (Intraocular Pressure) increases, it slowly compresses and damages the delicate fibers of the optic nerve. By the time a patient notices blind spots in their peripheral vision, substantial and irreversible nerve damage has already occurred.</p><h4>How is it diagnosed?</h4><p>A simple eyeglass test is not enough. A detailed Glaucoma workup includes: <ul><li><strong>Tonometry:</strong> Checking the eye pressure.</li><li><strong>Gonioscopy:</strong> Inspecting the drainage angles.</li><li><strong>Perimetry:</strong> Mapping your visual field limits.</li><li><strong>OCT Scan:</strong> Measuring the optic nerve fiber thickness.</li></ul></p><h4>Can it be treated?</h4><p>Yes, while the vision already lost cannot be restored, further damage can be effectively arrested using daily eye drops, laser procedures (SLT), or drainage surgery (Trabeculectomy). Early detection is the only shield!</p>",
            'category_id' => 'cat2',
            'tags' => ['Glaucoma', 'Optic Nerve', 'Prevention'],
            'featured_image' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?auto=format&fit=crop&w=800&q=80',
            'author' => 'Dr. Rajesh K. Patel',
            'published_at' => '2026-06-28 09:30:00',
            'status' => 'published',
            'meta_title' => 'Glaucoma Screening and Early Detection - Dr. Rajesh Patel',
            'meta_description' => 'Understand why Glaucoma is silent, how it damages the optic nerve, and the diagnostic tests essential for saving your vision.'
        ]);

        // 8. Events
        Event::create([
            'id' => 'e1',
            'title' => 'Free Cataract & Glaucoma Screening Camp',
            'description' => 'Madhav Netralaya is organizing a mega community eye-care outreach program. Offering free vision testing, intraocular pressure measurement, cataract screening, and subsidized surgeries for underprivileged families.',
            'date' => '2026-07-20',
            'time' => '09:00 AM - 04:00 PM',
            'location' => 'Madhav Netralaya Nagpur Main Campus & Rural Centers',
            'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=800&q=80',
            'status' => 'upcoming',
            'gallery' => [
                'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=600&q=80'
            ],
            'registrations_count' => 45
        ]);

        Event::create([
            'id' => 'e2',
            'title' => 'Ophthalmic Surgical Masterclass 2026',
            'description' => 'A prestigious CME and live-surgery demonstration for post-graduates and practicing ophthalmologists, highlighting advanced vitrectomy and premium Toric/Multifocal lens placements.',
            'date' => '2026-08-10',
            'time' => '10:00 AM - 05:00 PM',
            'location' => 'Madhav Netralaya Auditorum, Nagpur',
            'image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80',
            'status' => 'upcoming',
            'gallery' => [],
            'registrations_count' => 12
        ]);

        // 9. Testimonials
        Testimonial::create([
            'id' => 't1',
            'patient_name' => 'Gopal Rao Deshmukh',
            'age' => 68,
            'treatment' => 'Multifocal Cataract Surgery',
            'rating' => 5,
            'comment' => 'I was highly apprehensive about my cataract surgery due to my diabetes. But Dr. Madhav Dhomne and his wonderful nursing team guided me so gently. The procedure was totally painless, and now I can read newspapers and drive without spectacles!',
            'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
            'approved' => true,
            'created_at' => '2026-05-10 12:00:00'
        ]);

        Testimonial::create([
            'id' => 't2',
            'patient_name' => 'Priyanka Nair',
            'age' => 26,
            'treatment' => 'Custom Bladeless LASIK',
            'rating' => 5,
            'comment' => 'My spectacles had a high power of -6.5. Dr. Ananya Iyer performed custom bladeless LASIK on both my eyes. The freedom from specs is absolutely magical! Highly recommend Madhav Netralaya for LASIK.',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'approved' => true,
            'created_at' => '2026-06-01 15:00:00'
        ]);

        // 10. Gallery Items
        GalleryItem::create([
            'id' => 'g1',
            'title' => 'Advanced Pentacam Topography Room',
            'category' => 'infrastructure',
            'image_url' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80',
            'created_at' => '2026-01-10 00:00:00'
        ]);

        GalleryItem::create([
            'id' => 'g2',
            'title' => 'Dr. Madhav performing vitrectomy',
            'category' => 'doctors',
            'image_url' => 'https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=800&q=80',
            'created_at' => '2026-02-15 00:00:00'
        ]);

        GalleryItem::create([
            'id' => 'g3',
            'title' => 'Nagpur Eye Camp Distribution',
            'category' => 'eye_camps',
            'image_url' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
            'created_at' => '2026-03-20 00:00:00'
        ]);

        // 11. Notices
        Notice::create([
            'id' => 'n1',
            'title' => 'NABH Accreditation Achievement',
            'content' => 'We are extremely proud to announce that Madhav Netralaya has successfully renewed its tertiary-level NABH accreditation, confirming our commitment to the highest quality surgical and clinical protocols.',
            'category' => 'general',
            'date' => '2026-06-20',
            'important' => true
        ]);

        Notice::create([
            'id' => 'n2',
            'title' => 'Free Diabetic Retinopathy Camp - July 5th',
            'content' => 'Special Screening camp for all diabetic patients. Free retinal fundus photo evaluation and specialist counseling from 9 AM to 2 PM.',
            'category' => 'camp',
            'date' => '2026-07-05',
            'important' => true
        ]);

        Notice::create([
            'id' => 'n3',
            'title' => 'Public Holiday OPD Timings Notice',
            'content' => 'On account of public holiday on August 15th, OPD consults will remain closed. However, 24/7 Trauma, Accident, and Ocular Emergency services will function normally.',
            'category' => 'holiday',
            'date' => '2026-08-15',
            'important' => false
        ]);

        // 12. Sliders
        Slider::create([
            'id' => 's1',
            'title' => 'Pioneering Vitreoretinal & Advanced Cataract Care',
            'subtitle' => 'Nagpur\'s leading tertiary eye institute with NABH accreditation and elite diagnostic expertise.',
            'background_image' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=1600&q=80',
            'cta_text' => 'Meet Our Experts',
            'cta_link' => '/doctors',
            'order' => 1
        ]);

        Slider::create([
            'id' => 's2',
            'title' => 'Blade-Free LASIK Refractive Visual Freedom',
            'subtitle' => 'Get rid of specs permanently under the guidance of top cornea specialists utilizing global technologies.',
            'background_image' => 'https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=1600&q=80',
            'cta_text' => 'Read About LASIK',
            'cta_link' => '/departments?id=cornea',
            'order' => 2
        ]);

        Slider::create([
            'id' => 's3',
            'title' => 'Noble Vision: Rotary Madhav Eye Bank',
            'subtitle' => 'Join us in the fight against corneal blindness. Pledge your eyes, light up lives.',
            'background_image' => 'https://images.unsplash.com/photo-1530026405186-ed1ea0ac7a63?auto=format&fit=crop&w=1600&q=80',
            'cta_text' => 'Pledge Eyes',
            'cta_link' => '/about#eyebank',
            'order' => 3
        ]);

        // 13. Settings
        Setting::create([
            'id' => 'main_settings',
            'logo' => 'M',
            'favicon' => '👁️',
            'hospital_name' => 'Madhav Netralaya Eye Institute & Research Centre',
            'tagline' => 'Your Vision, Our Focused Expertise. NABH Accredited Tertiary Eye Care.',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0d9488',
            'address' => 'Madhav Netralaya, Near Jaitala Square, Hingna Road, Nagpur, Maharashtra, India - 440022',
            'phone_numbers' => ["+91 712 222 3344", "+91 712 222 3345", "+91 91234 56789"],
            'emails' => ["info@madhavnetralaya.org", "appointments@madhavnetralaya.org", "hr@madhavnetralaya.org"],
            'working_hours' => [
                'weekdays' => '09:00 AM - 08:00 PM',
                'saturday' => '09:00 AM - 06:00 PM',
                'sunday' => 'Closed (Emergency Only)'
            ],
            'emergency_contacts' => ["+91 712 123 4567", "+91 99887 76655"],
            'google_map_embed_url' => "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3722.213645851493!2d79.0152431!3d21.1039868!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bd4beed14555555%3A0xea694da451d8b671!2sMadhav%20Netralaya%20Eye%20Hospital%20Nagpur!5e0!3m2!1sen!2sin!4v1700000000000!5m2!1sen!2sin",
            'whatsapp_number' => '+919123456789',
            'social_media' => [
                'facebook' => 'https://facebook.com/madhavnetralaya',
                'twitter' => 'https://twitter.com/madhavnetralaya',
                'instagram' => 'https://instagram.com/madhavnetralaya',
                'youtube' => 'https://youtube.com/c/madhavnetralaya',
                'linkedin' => 'https://linkedin.com/company/madhavnetralaya'
            ],
            'google_analytics_id' => 'G-EYECARE123',
            'google_search_console_verification' => 'google-site-verification-12345',
            'email_smtp' => [
                'host' => 'smtp.madhavnetralaya.org',
                'port' => 587,
                'secure' => false,
                'user' => 'no-reply@madhavnetralaya.org'
            ]
        ]);

        // 14. SEO Meta
        SeoMeta::create([
            'id' => 'seo_home',
            'page_key' => 'home',
            'title' => 'Madhav Netralaya Eye Institute & Research Centre Nagpur',
            'description' => "Nagpur's premier NABH accredited tertiary eye hospital. Advanced Vitreoretinal care, Micro-Incision Cataract, custom LASIK, glaucoma, and pediatric ophthalmology.",
            'keywords' => ["eye hospital nagpur", "retina specialist nagpur", "lasik surgery nagpur", "best cataract doctor", "madhav netralaya", "eye surgery clinic"],
            'canonical_url' => 'https://new.madhavnetralaya.org',
            'og_type' => 'website',
            'og_image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=1200&q=80'
        ]);

        SeoMeta::create([
            'id' => 'seo_about',
            'page_key' => 'about',
            'title' => 'About Us - Madhav Netralaya Eye Institute Nagpur',
            'description' => 'Learn about Nagpur\'s finest eye hospital, our history, vision, mission, our esteemed leaders, and our state-of-the-art eye bank & research services.',
            'keywords' => ["about eye hospital nagpur", "rotary eye bank nagpur", "nabh eye hospital", "madhav netralaya history"],
            'canonical_url' => 'https://new.madhavnetralaya.org/about',
            'og_type' => 'article',
            'og_image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&w=1200&q=80'
        ]);
    }
}
