<?php
require_once __DIR__ . '/../../app/SupabaseClient.php';

function getSupabase()
{
    return new \App\SupabaseClient(SUPABASE_URL, SUPABASE_SERVICE_KEY, SUPABASE_ANON_KEY);
}

function getBlockDefinitions()
{
    return array(
        'hero' => array(
            'hero_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Zīmola vadītāja un mārketinga stratēģe',
                'placeholder_en' => 'Brand Manager & Marketing Strategist'),
            'hero_title' => array('label' => 'Virsraksts (kursīvs: <em>teksts</em>)', 'type' => 'text',
                'placeholder' => 'Pārvēršu idejas <em>realitātē</em>',
                'placeholder_en' => 'I transform ideas <em>into new reality</em>'),
            'hero_subtitle' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'Esmu veltīta zīmolu veidošanai, produktu attīstībai un pārdošanas aktivizācijai - ar 10+ gadu pieredzi pārveidoju vīzijas mērāmos rezultātos.',
                'placeholder_en' => "I'm dedicated to brand building, product development, and sales activation - with 10+ years of experience turning visions into measurable results."),
        ),
        'about' => array(
            'about_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Par mani',
                'placeholder_en' => 'About'),
            'about_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text',
                'placeholder' => 'Prāts<br>aiz zīmoliem',
                'placeholder_en' => 'The mind<br>behind the brands'),
            'about_text_1' => array('label' => '1. punkts', 'type' => 'textarea',
                'placeholder' => 'Esmu premium zīmolu stratēģe un radoša multimāksliniece reklāmas industrijā, palīdzot zīmoliem kļūt pamanāmiem, magnētiskiem un pelnošiem bez trokšņa.',
                'placeholder_en' => 'I am a premium brand strategist and creative multimedia artist in the advertising industry, helping brands become visible, magnetic, and desirable without the noise.'),
            'about_list' => array('label' => 'Saraksts', 'type' => 'list',
                'placeholder' => 'Zīmola stratēģija un pozicionēšana',
                'placeholder_en' => 'Brand strategy & positioning'),
            'about_text_2' => array('label' => '2. punkts', 'type' => 'textarea',
                'placeholder' => 'Ticu, ka īsta autoritāte nav skaļums, bet klātbūtne, kuru nevar ignorēt.',
                'placeholder_en' => "I believe true authority is not about volume, but a presence that cannot be ignored."),
        ),
        'stats' => array(
            'stats_group' => array('label' => 'Statistika', 'type' => 'stats'),
        ),
        'services' => array(
            'services_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Pakalpojumi',
                'placeholder_en' => 'Services'),
            'services_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text',
                'placeholder' => 'Piedāvāju pilna cikla mārketingu,<br>no gala līdz galam',
                'placeholder_en' => 'Full-cycle marketing,<br>end-to-end excellence'),
            'services_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'No zīmola pamatiem līdz veiktspēju veicinošām kampaņām.',
                'placeholder_en' => 'From brand foundations to performance-driven campaigns, I tailor every service to build brands that resonate and convert.'),
        ),
        'missis' => array(
            'missis_badge' => array('label' => 'Mazā etiķetes teksts', 'type' => 'text',
                'placeholder' => '2026 Fināliste',
                'placeholder_en' => '2026 Finalist'),
            'missis_title' => array('label' => 'Virsraksts (jauna rinda: <br>, kursīvs: <em>teksts</em>)', 'type' => 'text',
                'placeholder' => 'Missis Latvia<br><em>2026 Charm</em>',
                'placeholder_en' => 'Missis Latvia<br><em>2026 Charm</em>'),
            'missis_text_1' => array('label' => '1. punkts', 'type' => 'textarea',
                'placeholder' => 'Aiz biroja sienām un zīmolu prezentācijām es lepni pārstāvu Baltijas reģionu kā Missis Latvia 2026.',
                'placeholder_en' => 'Beyond the boardroom and brand decks, I proudly represent the Baltic region as Missis Latvia 2026 - a celebration of resilience, self-belief, and the power of inner strength.'),
            'missis_text_2' => array('label' => '2. punkts (spēcīgs: <strong>teksts</strong>)', 'type' => 'textarea',
                'placeholder' => 'Skatuve un mārketinga pasaule dalās ar vienu patiesību: autentiskums piesaista uzmanību.',
                'placeholder_en' => 'The pageant stage and the marketing stage share the same truth: authenticity commands attention.'),
            'missis_url' => array('label' => 'Saites adrese (URL)', 'type' => 'text',
                'placeholder' => '',
                'placeholder_en' => ''),
            'missis_btn' => array('label' => 'Pogas uzraksts', 'type' => 'text',
                'placeholder' => '',
                'placeholder_en' => '',
                'hint' => 'Ja URL un uzraksts nav ievadīts, poga netiks rādīta'),
        ),
        'experience' => array(
            'exp_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Ekspertīze',
                'placeholder_en' => 'Expertise'),
            'exp_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text',
                'placeholder' => 'Kur stratēģija<br>satiek izpildi',
                'placeholder_en' => 'Where strategy<br>meets execution'),
            'exp_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'Man ir pieredze vairākās nozarēs.',
                'placeholder_en' => 'I bring multi-industry experience spanning brand management, corporate communication, project leadership, and business development.'),
        ),
        'testimonials' => array(
            'test_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Atsauksmes',
                'placeholder_en' => 'Testimonials'),
            'test_title' => array('label' => 'Virsraksts', 'type' => 'text',
                'placeholder' => 'Ko citi saka',
                'placeholder_en' => 'What clients say'),
            'test_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'Es veidoju partnerības uz uzticības, rezultātu un kopīgas apņemšanās izcilībai.',
                'placeholder_en' => 'I build partnerships on trust, results, and a shared commitment to excellence.'),
        ),
        'contact' => array(
            'contact_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Kontakti',
                'placeholder_en' => 'Contact'),
            'contact_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text',
                'placeholder' => 'Izveidosim kaut ko<br>ievērojamu',
                'placeholder_en' => "Let's build something<br>remarkable"),
            'contact_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'Gatavs pārveidot savu zīmolu?',
                'placeholder_en' => "Ready to transform your brand? Let's talk about how we can create meaningful impact together."),
            'contact_email' => array('label' => 'E-pasts', 'type' => 'text',
                'placeholder' => 'lueta@lueta.lv',
                'placeholder_en' => 'lueta@lueta.lv'),
            'contact_location' => array('label' => 'Atrašanās vieta', 'type' => 'text',
                'placeholder' => 'Rīga, Latvija',
                'placeholder_en' => 'Riga, Latvia'),
            'contact_cta_title' => array('label' => 'Aicinājuma (CTA) virsraksts (jauna rinda: <br>)', 'type' => 'text',
                'placeholder' => 'Ir ideja<br>projektam?',
                'placeholder_en' => 'Have a project<br>in mind?'),
            'contact_cta_text' => array('label' => 'Aicinājuma (CTA) apraksts', 'type' => 'textarea',
                'placeholder' => 'Sāksim sarunu par jūsu zīmola nākotni.',
                'placeholder_en' => "Let's start a conversation about your brand's future."),
            'contact_cta_btn' => array('label' => 'Aicinājuma (CTA) pogas uzraksts', 'type' => 'text',
                'placeholder' => 'Sazināties',
                'placeholder_en' => 'Get in Touch'),
        ),
        'footer' => array(
            'footer_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'Esmu mārketinga stratēģe ar 10+ gadu pieredze.',
                'placeholder_en' => "I'm a Brand Manager & Marketing Strategist with 10+ years of experience. I build brands that transform ideas into new reality."),
        ),
        'images' => array(
            'hero_images' => array('label' => 'Hero attēli', 'type' => 'image'),
            'missis_images' => array('label' => 'Papildus info attēli', 'type' => 'image'),
        ),
        'blog' => array(
            'blog_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text',
                'placeholder' => 'Blog',
                'placeholder_en' => 'Blog'),
            'blog_title' => array('label' => 'Virsraksts', 'type' => 'text',
                'placeholder' => 'Jaunumi',
                'placeholder_en' => 'Latest news'),
            'blog_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea',
                'placeholder' => 'Iedvesma, stāsti un jaunumi no mana ceļojuma.',
                'placeholder_en' => 'Insights, stories and news from my journey.'),
        ),
    );
}

function getExistingBlocks($supabase, $page)
{
    $result = $supabase->select('content_blocks', array(
        'page' => 'eq.' . $page,
        'select' => 'section,block_key,block_value,id',
    ));
    if ($result === null || isset($result['error'])) return array();
    $blocks = array();
    foreach ($result as $row) {
        $blocks[$row['block_key']] = $row;
    }
    return $blocks;
}

function saveContentBlock($supabase, $page, $section, $key, $value)
{
    $existing = $supabase->select('content_blocks', array(
        'page' => 'eq.' . $page,
        'block_key' => 'eq.' . $key,
        'select' => 'id',
    ));
    if ($existing && !isset($existing['error']) && count($existing) > 0) {
        $result = $supabase->update('content_blocks', array(
            'block_value' => $value,
            'updated_at' => date('c'),
        ), array(
            'page' => 'eq.' . $page,
            'block_key' => 'eq.' . $key,
        ));
        return $result !== null && !isset($result['error']);
    } else {
        $result = $supabase->insert('content_blocks', array(
            'page' => $page,
            'section' => $section,
            'block_key' => $key,
            'block_value' => $value,
        ));
        return $result !== null && !isset($result['error']);
    }
}

function optimizeImage($path, $maxWidth = 1920, $quality = 82, $maxSizeKB = 500)
{
    if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) return false;
    if (!is_file($path)) return false;

    $info = @getimagesize($path);
    if (!$info) return false;

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];
    $fileSizeKB = filesize($path) / 1024;

    if ($width <= $maxWidth && $fileSizeKB <= $maxSizeKB) return 'skip';

    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int) round($height * ($maxWidth / $width));
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $src = null;
    switch ($mime) {
        case 'image/jpeg': $src = imagecreatefromjpeg($path); break;
        case 'image/png':  $src = imagecreatefrompng($path); break;
        case 'image/webp': $src = imagecreatefromwebp($path); break;
        default: return false;
    }
    if (!$src) return false;

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $path);
    $saved = false;

    if (function_exists('imagewebp')) {
        $saved = imagewebp($dst, $webpPath, $quality);
        if ($saved && $webpPath !== $path) {
            imagedestroy($src);
            imagedestroy($dst);
            unlink($path);
            return $webpPath;
        }
    }

    switch ($mime) {
        case 'image/jpeg': $saved = imagejpeg($dst, $path, $quality); break;
        case 'image/png':  $saved = imagepng($dst, $path, round(9 - ($quality / 100) * 9)); break;
        case 'image/webp': $saved = imagewebp($dst, $path, $quality); break;
    }

    imagedestroy($src);
    imagedestroy($dst);
    return $saved ? $path : false;
}
