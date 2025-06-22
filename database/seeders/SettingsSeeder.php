<?php

namespace Database\Seeders;

use App\Settings\GeneralSettings;
use App\Settings\LocationSettings;
use App\Settings\ShopSettings;
use App\Settings\SocialSettings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // General Settings
        $generalSettings = app(GeneralSettings::class);
        if (empty($generalSettings->site_name)) {
            $generalSettings->site_name = 'Artwork Shop';
            $generalSettings->site_description = 'Your one-stop shop for artwork and creative supplies';
            $generalSettings->site_email = 'info@artworkshop.com';
            $generalSettings->site_phone = '+1234567890';
            $generalSettings->site_author = 'Artwork Shop Team';
            $generalSettings->site_keywords = 'artwork, shop, creative, supplies, art';
            $generalSettings->site_logo = null;
            $generalSettings->site_profile = null;
            $generalSettings->save();
        }

        // Social Settings
        $socialSettings = app(SocialSettings::class);
        if (empty($socialSettings->site_social)) {
            $socialSettings->site_social = [
                [
                    'vendor' => 'facebook',
                    'link' => 'https://facebook.com/artworkshop',
                ],
                [
                    'vendor' => 'instagram',
                    'link' => 'https://instagram.com/artworkshop',
                ],
                [
                    'vendor' => 'twitter',
                    'link' => 'https://twitter.com/artworkshop',
                ],
            ];
            $socialSettings->save();
        }

    }
}
