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
            'hero_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text', 'placeholder' => 'Zīmola vadītāja un mārketinga stratēģe'),
            'hero_title' => array('label' => 'Virsraksts (kursīvs: <em>teksts</em>)', 'type' => 'text', 'placeholder' => 'Pārvēršu idejas <em>realitātē</em>'),
            'hero_subtitle' => array('label' => 'Satura apraksts', 'type' => 'textarea', 'placeholder' => 'Esmu veltīta zīmolu veidošanai, produktu attīstībai un pārdošanas aktivizācijai - ar 10+ gadu pieredzi pārveidoju vīzijas mērāmos rezultātos.'),
        ),
        'about' => array(
            'about_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text', 'placeholder' => 'Par mani'),
            'about_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text', 'placeholder' => 'Prāts<br>aiz zīmoliem'),
            'about_text_1' => array('label' => '1. punkts', 'type' => 'textarea', 'placeholder' => 'Esmu premium zīmolu stratēģe un radoša multimāksliniece reklāmas industrijā, palīdzot zīmoliem kļūt pamanāmiem, magnētiskiem un pelnošiem bez trokšņa.'),
            'about_list' => array('label' => 'Saraksts', 'type' => 'list', 'placeholder' => 'Zīmola stratēģija un pozicionēšana'),
            'about_text_2' => array('label' => '2. punkts', 'type' => 'textarea', 'placeholder' => 'Ticu, ka īsta autoritāte nav skaļums, bet klātbūtne, kuru nevar ignorēt.'),
        ),
        'stats' => array(
            'stats_group' => array('label' => 'Statistika', 'type' => 'stats'),
        ),
        'services' => array(
            'services_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text', 'placeholder' => 'Pakalpojumi'),
            'services_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text', 'placeholder' => 'Piedāvāju pilna cikla mārketingu,<br>no gala līdz galam'),
            'services_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea', 'placeholder' => 'No zīmola pamatiem līdz veiktspēju veicinošām kampaņām.'),
        ),
        'missis' => array(
            'missis_badge' => array('label' => 'Mazā etiķetes teksts', 'type' => 'text', 'placeholder' => '2026 Fināliste'),
            'missis_title' => array('label' => 'Virsraksts (jauna rinda: <br>, kursīvs: <em>teksts</em>)', 'type' => 'text', 'placeholder' => 'Missis Latvia<br><em>2026 Charm</em>'),
            'missis_text_1' => array('label' => '1. punkts', 'type' => 'textarea', 'placeholder' => 'Aiz biroja sienām un zīmolu prezentācijām es lepni pārstāvu Baltijas reģionu kā Missis Latvia 2026.'),
            'missis_text_2' => array('label' => '2. punkts (spēcīgs: <strong>teksts</strong>)', 'type' => 'textarea', 'placeholder' => 'Skatuve un mārketinga pasaule dalās ar vienu patiesību: autentiskums piesaista uzmanību.'),
            'missis_url' => array('label' => 'Saites adrese (URL)', 'type' => 'text', 'placeholder' => 'https://lnkd.in/dyT2bhkM'),
            'missis_btn' => array('label' => 'Pogas uzraksts', 'type' => 'text', 'placeholder' => 'Skatīt ceļojumu'),
        ),
        'experience' => array(
            'exp_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text', 'placeholder' => 'Ekspertīze'),
            'exp_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text', 'placeholder' => 'Kur stratēģija<br>satiek izpildi'),
            'exp_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea', 'placeholder' => 'Man ir pieredze vairākās nozarēs.'),
        ),
        'testimonials' => array(
            'test_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text', 'placeholder' => 'Atsauksmes'),
            'test_title' => array('label' => 'Virsraksts', 'type' => 'text', 'placeholder' => 'Ko citi saka'),
            'test_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea', 'placeholder' => 'Es veidoju partnerības uz uzticības, rezultātu un kopīgas apņemšanās izcilībai.'),
        ),
        'contact' => array(
            'contact_tag' => array('label' => 'Sadaļas augšteksts', 'type' => 'text', 'placeholder' => 'Kontakti'),
            'contact_title' => array('label' => 'Virsraksts (jauna rinda: <br>)', 'type' => 'text', 'placeholder' => 'Izveidosim kaut ko<br>ievērojamu'),
            'contact_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea', 'placeholder' => 'Gatavs pārveidot savu zīmolu?'),
            'contact_email' => array('label' => 'E-pasts', 'type' => 'text', 'placeholder' => 'lueta@lueta.lv'),
            'contact_location' => array('label' => 'Atrašanās vieta', 'type' => 'text', 'placeholder' => 'Rīga, Latvija'),
            'contact_cta_title' => array('label' => 'Aicinājuma (CTA) virsraksts (jauna rinda: <br>)', 'type' => 'text', 'placeholder' => 'Ir ideja<br>projektam?'),
            'contact_cta_text' => array('label' => 'Aicinājuma (CTA) apraksts', 'type' => 'textarea', 'placeholder' => 'Sāksim sarunu par jūsu zīmola nākotni.'),
            'contact_cta_btn' => array('label' => 'Aicinājuma (CTA) pogas uzraksts', 'type' => 'text', 'placeholder' => 'Sazināties'),
        ),
        'footer' => array(
            'footer_desc' => array('label' => 'Satura apraksts', 'type' => 'textarea', 'placeholder' => 'Esmu mārketinga stratēģe ar 10+ gadu pieredze.'),
        ),
        'images' => array(
            'hero_images' => array('label' => 'Hero attēli', 'type' => 'image'),
            'missis_images' => array('label' => 'Papildus info attēli', 'type' => 'image'),
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
