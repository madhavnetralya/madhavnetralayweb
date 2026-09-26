<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class InstagramController
{
    private const FALLBACK_INSTAGRAM_POSTS = [
        [
            'id' => 'ig_fallback_1',
            'media_type' => 'IMAGE',
            'media_url' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=600&q=80',
            'permalink' => 'https://www.instagram.com/madhavnetralaya/?hl=en',
            'caption' => '🌟 Empowering vision in Central India! Sharing highlights from our latest free Ophthalmic Screening Camp. Over 150 patients were screened for cataracts, glaucoma, and diabetic retinopathy. Together, let\'s eradicate preventable visual blindness! 👁️✨ #MadhavNetralaya #EyeCareNagpur #FreeEyeCamp #HealthyVision',
            'timestamp' => '2026-07-15T10:00:00+0000',
            'username' => 'madhavnetralaya',
            'likes_count' => 142,
            'comments_count' => 8
        ],
        [
            'id' => 'ig_fallback_2',
            'media_type' => 'IMAGE',
            'media_url' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=600&q=80',
            'permalink' => 'https://www.instagram.com/madhavnetralaya/?hl=en',
            'caption' => '🏥 Excellence you can trust. Madhav Netralaya is proud to be a tertiary-level NABH Accredited Eye Institute. This represents our unwavering commitment to top-tier patient safety, infection control protocols, and clinical outcomes. 🎖️❤️ #NABH #QualityHealthcare #NagpurHospitals #PatientCareFirst',
            'timestamp' => '2026-07-12T08:30:00+0000',
            'username' => 'madhavnetralaya',
            'likes_count' => 98,
            'comments_count' => 4
        ],
        [
            'id' => 'ig_fallback_3',
            'media_type' => 'IMAGE',
            'media_url' => 'https://images.unsplash.com/photo-1530026405186-ed1ea0ac7a63?auto=format&fit=crop&w=600&q=80',
            'permalink' => 'https://www.instagram.com/madhavnetralaya/?hl=en',
            'caption' => '👁️ Donating eyes is giving the gift of sight. Rotary Madhav Eye Bank operates 24/7 in Nagpur to facilitate noble corneal harvests. A single pledge can restore vision for two individuals. Take the step, pledge your eyes today! 🕊️🤝 #EyeDonation #GiftOfSight #RotaryMadhavEyeBank #EradicateBlindness',
            'timestamp' => '2026-07-09T14:15:00+0000',
            'username' => 'madhavnetralaya',
            'likes_count' => 215,
            'comments_count' => 16
        ],
        [
            'id' => 'ig_fallback_4',
            'media_type' => 'IMAGE',
            'media_url' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=600&q=80',
            'permalink' => 'https://www.instagram.com/madhavnetralaya/?hl=en',
            'caption' => '⚡ Say goodbye to specs! Experience crystal-clear, blade-free customized LASIK and advanced refractive procedures. Under the expert guidance of Dr. Ananya Iyer and our premium cornea department, visual freedom is just a session away. 👓✨ #LASIKNagpur #SpecsRemoval #RefractiveSurgery #ClearVision',
            'timestamp' => '2026-07-05T11:00:00+0000',
            'username' => 'madhavnetralaya',
            'likes_count' => 176,
            'comments_count' => 12
        ],
        [
            'id' => 'ig_fallback_5',
            'media_type' => 'IMAGE',
            'media_url' => 'https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=600&q=80',
            'permalink' => 'https://www.instagram.com/madhavnetralaya/?hl=en',
            'caption' => '👶 Early diagnosis protects a child\'s future. Our Paediatric Ophthalmology wing provides child-friendly screenings for squints, lazy eyes, and refractive errors. Ensure your little one\'s vision is set for success! 🧸🎈 #PediatricEyeCare #SquintTreatment #ChildrensHealth #MadhavNetralaya',
            'timestamp' => '2026-07-02T09:45:00+0000',
            'username' => 'madhavnetralaya',
            'likes_count' => 112,
            'comments_count' => 5
        ],
        [
            'id' => 'ig_fallback_6',
            'media_type' => 'IMAGE',
            'media_url' => 'https://images.unsplash.com/photo-1551601651-2a8555f1a136?auto=format&fit=crop&w=600&q=80',
            'permalink' => 'https://www.instagram.com/madhavnetralaya/?hl=en',
            'caption' => '🔬 Advanced Vitreoretinal surgery, guided by pioneer Dr. Madhav S. Dhomne. We specialize in complex retina detachments and diabetic retinopathy management utilizing state-of-the-art diagnostic imaging and high-end microscopes. 👁️💻 #RetinaSpecialist #VitreoretinalSurgery #EyeCareExcellence #MadhavNetralaya',
            'timestamp' => '2026-06-29T13:00:00+0000',
            'username' => 'madhavnetralaya',
            'likes_count' => 189,
            'comments_count' => 10
        ]
    ];

    public function getPosts()
    {
        try {
            // Get state from database
            $row = DB::table('state_store')->where('key', 'state')->first();
            $token = null;
            
            if ($row) {
                $state = json_decode($row->value, true);
                $token = $state['settings']['instagramAccessToken'] ?? null;
            }
            
            // Fallback to env
            if (!$token) {
                $token = env('INSTAGRAM_ACCESS_TOKEN');
            }

            // Check cache
            if (Cache::has('instagram_feed')) {
                return response()->json([
                    'success' => true,
                    'posts' => Cache::get('instagram_feed'),
                    'source' => 'cache'
                ]);
            }

            if (!$token) {
                return response()->json([
                    'success' => true,
                    'posts' => self::FALLBACK_INSTAGRAM_POSTS,
                    'source' => 'fallback'
                ]);
            }

            // Fetch from Instagram Graph API
            $url = "https://graph.instagram.com/me/media";
            $response = Http::get($url, [
                'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username',
                'access_token' => $token
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']) && is_array($data['data'])) {
                    // Cache for 1 hour (3600 seconds)
                    Cache::put('instagram_feed', $data['data'], 3600);
                    
                    return response()->json([
                        'success' => true,
                        'posts' => $data['data'],
                        'source' => 'api'
                    ]);
                }
                
                throw new \Exception("Invalid response format from Instagram API");
            }

            throw new \Exception("Instagram API returned status {$response->status()}: {$response->body()}");

        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'posts' => self::FALLBACK_INSTAGRAM_POSTS,
                'source' => 'fallback',
                'error' => $e->getMessage()
            ]);
        }
    }
}
