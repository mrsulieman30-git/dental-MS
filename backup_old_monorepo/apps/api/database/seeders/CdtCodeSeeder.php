<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CdtCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function up(): void
    {
        $codes = [
            ['code' => 'D0120', 'category' => 'diagnostic', 'description' => 'Periodic oral evaluation - established patient'],
            ['code' => 'D0140', 'category' => 'diagnostic', 'description' => 'Limited oral evaluation - problem focused'],
            ['code' => 'D0150', 'category' => 'diagnostic', 'description' => 'Comprehensive oral evaluation - new or established patient'],
            ['code' => 'D0210', 'category' => 'diagnostic', 'description' => 'Intraoral - comprehensive series of radiographic images'],
            ['code' => 'D0220', 'category' => 'diagnostic', 'description' => 'Intraoral - periapical first radiographic image'],
            ['code' => 'D0274', 'category' => 'diagnostic', 'description' => 'Bitewings - four radiographic images'],
            ['code' => 'D0330', 'category' => 'diagnostic', 'description' => 'Panoramic radiographic image'],
            ['code' => 'D1110', 'category' => 'preventive', 'description' => 'Prophylaxis - adult'],
            ['code' => 'D1120', 'category' => 'preventive', 'description' => 'Prophylaxis - child'],
            ['code' => 'D1206', 'category' => 'preventive', 'description' => 'Topical application of fluoride varnish'],
            ['code' => 'D1351', 'category' => 'preventive', 'description' => 'Sealant - per tooth'],
            ['code' => 'D2140', 'category' => 'restorative', 'description' => 'Amalgam - one surface, primary or permanent'],
            ['code' => 'D2330', 'category' => 'restorative', 'description' => 'Resin-based composite - one surface, anterior'],
            ['code' => 'D2391', 'category' => 'restorative', 'description' => 'Resin-based composite - one surface, posterior'],
            ['code' => 'D2740', 'category' => 'restorative', 'description' => 'Crown - porcelain/ceramic substrate'],
            ['code' => 'D3310', 'category' => 'endodontics', 'description' => 'Endodontic therapy, anterior tooth (excluding final restoration)'],
            ['code' => 'D3320', 'category' => 'endodontics', 'description' => 'Endodontic therapy, premolar tooth (excluding final restoration)'],
            ['code' => 'D3330', 'category' => 'endodontics', 'description' => 'Endodontic therapy, molar tooth (excluding final restoration)'],
            ['code' => 'D4341', 'category' => 'periodontics', 'description' => 'Periodontal scaling and root planing - four or more teeth per quadrant'],
            ['code' => 'D4910', 'category' => 'periodontics', 'description' => 'Periodontal maintenance'],
            ['code' => 'D5110', 'category' => 'prosthodontics', 'description' => 'Complete denture - maxillary'],
            ['code' => 'D6010', 'category' => 'prosthodontics', 'description' => 'Surgical placement of implant body: endosteal implant'],
            ['code' => 'D7140', 'category' => 'maxillofacial', 'description' => 'Extraction, erupted tooth or exposed root'],
            ['code' => 'D7210', 'category' => 'maxillofacial', 'description' => 'Extraction, erupted tooth requiring removal of bone and/or sectioning of tooth'],
            ['code' => 'D8080', 'category' => 'orthodontics', 'description' => 'Comprehensive orthodontic treatment of the adolescent dentition'],
            ['code' => 'D9110', 'category' => 'adjunctive', 'description' => 'Palliative (emergency) treatment of dental pain - minor procedure'],
        ];

        foreach ($codes as $code) {
            DB::table('cdt_codes')->updateOrInsert(
                ['code' => $code['code']],
                [
                    'id' => Str::uuid(),
                    'description' => $code['description'],
                    'category' => $code['category'],
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
        }
    }
}
