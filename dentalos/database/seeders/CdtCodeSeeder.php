<?php

namespace Database\Seeders;

use App\Models\CdtCode;
use Illuminate\Database\Seeder;

class CdtCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            ['code' => 'D0120', 'short_description' => 'Periodic Oral Eval', 'description' => 'Periodic oral evaluation - established patient', 'category' => 'diagnostic'],
            ['code' => 'D0140', 'short_description' => 'Limited Oral Eval', 'description' => 'Limited oral evaluation - problem focused', 'category' => 'diagnostic'],
            ['code' => 'D0150', 'short_description' => 'Comp Oral Eval', 'description' => 'Comprehensive oral evaluation - new or established patient', 'category' => 'diagnostic'],
            ['code' => 'D0180', 'short_description' => 'Comp Perio Eval', 'description' => 'Comprehensive periodontal evaluation', 'category' => 'diagnostic'],
            ['code' => 'D0210', 'short_description' => 'FMX', 'description' => 'Intraoral - complete series of radiographic images', 'category' => 'diagnostic'],
            ['code' => 'D0220', 'short_description' => 'Periapical 1st Film', 'description' => 'Intraoral - periapical first radiographic image', 'category' => 'diagnostic'],
            ['code' => 'D0272', 'short_description' => 'Bitewings 2 Films', 'description' => 'Bitewings - two radiographic images', 'category' => 'diagnostic'],
            ['code' => 'D0274', 'short_description' => 'Bitewings 4 Films', 'description' => 'Bitewings - four radiographic images', 'category' => 'diagnostic'],
            ['code' => 'D0330', 'short_description' => 'Panoramic', 'description' => 'Panoramic radiographic image', 'category' => 'diagnostic'],
            ['code' => 'D1110', 'short_description' => 'Adult Prophy', 'description' => 'Prophylaxis - adult', 'category' => 'preventive'],
            ['code' => 'D1120', 'short_description' => 'Child Prophy', 'description' => 'Prophylaxis - child', 'category' => 'preventive'],
            ['code' => 'D1206', 'short_description' => 'Fluoride Varnish', 'description' => 'Topical application of fluoride varnish', 'category' => 'preventive'],
            ['code' => 'D1351', 'short_description' => 'Sealant', 'description' => 'Sealant - per tooth', 'category' => 'preventive'],
            ['code' => 'D2140', 'short_description' => 'Amalgam 1 Surf', 'description' => 'Amalgam - one surface, primary or permanent', 'category' => 'restorative'],
            ['code' => 'D2150', 'short_description' => 'Amalgam 2 Surf', 'description' => 'Amalgam - two surfaces, primary or permanent', 'category' => 'restorative'],
            ['code' => 'D2160', 'short_description' => 'Amalgam 3 Surf', 'description' => 'Amalgam - three surfaces, primary or permanent', 'category' => 'restorative'],
            ['code' => 'D2330', 'short_description' => 'Composite 1 Surf Ant', 'description' => 'Resin-based composite - one surface, anterior', 'category' => 'restorative'],
            ['code' => 'D2331', 'short_description' => 'Composite 2 Surf Ant', 'description' => 'Resin-based composite - two surfaces, anterior', 'category' => 'restorative'],
            ['code' => 'D2391', 'short_description' => 'Composite 1 Surf Post', 'description' => 'Resin-based composite - one surface, posterior', 'category' => 'restorative'],
            ['code' => 'D2392', 'short_description' => 'Composite 2 Surf Post', 'description' => 'Resin-based composite - two surfaces, posterior', 'category' => 'restorative'],
            ['code' => 'D2393', 'short_description' => 'Composite 3 Surf Post', 'description' => 'Resin-based composite - three surfaces, posterior', 'category' => 'restorative'],
            ['code' => 'D2394', 'short_description' => 'Composite 4+ Surf Post', 'description' => 'Resin-based composite - four or more surfaces, posterior', 'category' => 'restorative'],
            ['code' => 'D2740', 'short_description' => 'Porcelain Crown', 'description' => 'Crown - porcelain/ceramic substrate', 'category' => 'restorative'],
            ['code' => 'D2750', 'short_description' => 'PFM Crown', 'description' => 'Crown - porcelain fused to high noble metal', 'category' => 'restorative'],
            ['code' => 'D2950', 'short_description' => 'Core Buildup', 'description' => 'Core buildup, including any pins when required', 'category' => 'restorative'],
            ['code' => 'D3310', 'short_description' => 'RCT Anterior', 'description' => 'Endodontic therapy, anterior tooth', 'category' => 'endodontics'],
            ['code' => 'D3320', 'short_description' => 'RCT Premolar', 'description' => 'Endodontic therapy, premolar tooth', 'category' => 'endodontics'],
            ['code' => 'D3330', 'short_description' => 'RCT Molar', 'description' => 'Endodontic therapy, molar tooth', 'category' => 'endodontics'],
            ['code' => 'D4341', 'short_description' => 'SRP 4+ Teeth', 'description' => 'Periodontal scaling and root planing - four or more teeth per quadrant', 'category' => 'periodontics'],
            ['code' => 'D4342', 'short_description' => 'SRP 1-3 Teeth', 'description' => 'Periodontal scaling and root planing - one to three teeth per quadrant', 'category' => 'periodontics'],
            ['code' => 'D4910', 'short_description' => 'Perio Maintenance', 'description' => 'Periodontal maintenance', 'category' => 'periodontics'],
            ['code' => 'D4249', 'short_description' => 'Crown Lengthening', 'description' => 'Clinical crown lengthening - hard tissue', 'category' => 'periodontics'],
            ['code' => 'D5110', 'short_description' => 'Complete Upper Denture', 'description' => 'Complete denture - maxillary', 'category' => 'prosthodontics'],
            ['code' => 'D5120', 'short_description' => 'Complete Lower Denture', 'description' => 'Complete denture - mandibular', 'category' => 'prosthodontics'],
            ['code' => 'D6010', 'short_description' => 'Implant Body', 'description' => 'Surgical placement of implant body: endosteal implant', 'category' => 'prosthodontics'],
            ['code' => 'D6058', 'short_description' => 'Implant Crown', 'description' => 'Abutment supported porcelain/ceramic crown', 'category' => 'prosthodontics'],
            ['code' => 'D6750', 'short_description' => 'FPD Retainer Crown', 'description' => 'Retainer crown - porcelain fused to high noble metal', 'category' => 'prosthodontics'],
            ['code' => 'D7140', 'short_description' => 'Extraction Erupted', 'description' => 'Extraction, erupted tooth or exposed root', 'category' => 'adjunctive'],
            ['code' => 'D7210', 'short_description' => 'Surgical Extraction', 'description' => 'Extraction, erupted tooth requiring removal of bone', 'category' => 'adjunctive'],
            ['code' => 'D7230', 'short_description' => 'Impacted Partial Bony', 'description' => 'Removal of impacted tooth - partially bony', 'category' => 'adjunctive'],
            ['code' => 'D7240', 'short_description' => 'Impacted Full Bony', 'description' => 'Removal of impacted tooth - completely bony', 'category' => 'adjunctive'],
            ['code' => 'D9110', 'short_description' => 'Palliative Tx', 'description' => 'Palliative (emergency) treatment of dental pain', 'category' => 'adjunctive'],
            ['code' => 'D9230', 'short_description' => 'Nitrous Oxide', 'description' => 'Inhalation of nitrous oxide/analgesia, anxiolysis', 'category' => 'adjunctive'],
            ['code' => 'D8080', 'short_description' => 'Comp Ortho Adult', 'description' => 'Comprehensive orthodontic treatment of the adult dentition', 'category' => 'orthodontics'],
            ['code' => 'D8090', 'short_description' => 'Comp Ortho Adolescent', 'description' => 'Comprehensive orthodontic treatment of the adolescent dentition', 'category' => 'orthodontics'],
        ];

        foreach ($codes as $code) {
            CdtCode::updateOrCreate(
                ['code' => $code['code']],
                array_merge($code, ['is_active' => true, 'created_at' => now()])
            );
        }
    }
}
