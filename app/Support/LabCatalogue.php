<?php

namespace App\Support;

use App\Enums\TestDepartment;

class LabCatalogue
{
    /**
     * Official Sadat Salihi laboratory test form.
     *
     * @return list<array{name: string, department: string, price: float, normal_range: string, code: string}>
     */
    public static function tests(): array
    {
        return [
            ...self::map(TestDepartment::Routine, [
                ['CBC', 150, 'See CBC panel'],
                ['TLC', 80, '4.0-11.0 x10^3/µL'],
                ['HB', 70, 'Male 13-17 g/dL'],
                ['PLT', 80, '150-450 x10^3/µL'],
                ['HCT', 70, 'Male 40-50 %'],
                ['ESR', 80, 'Male <15 mm/hr'],
                ['Blood group', 100, 'A/B/AB/O ± Rh'],
                ['Reticulocyte count', 150, '0.5-1.5 %'],
                ['Coombs indirect', 200, 'Negative'],
                ['Coombs direct', 200, 'Negative'],
                ['Crossmatch', 250, 'Compatible'],
                ['Urine R/E', 100, 'See chemical / microscopic'],
                ['Semen analysis', 250, 'WHO reference'],
                ['Fluid analysis', 300, 'See report'],
                ['Urine ketone', 60, 'Negative'],
                ['Urine albumin', 60, 'Negative'],
                ['Urine glucose', 60, 'Negative'],
                ['Stone analysis', 400, 'See report'],
                ['Stool R/E', 100, 'No ova / cyst'],
                ['Stool H. pylori Ag', 250, 'Negative'],
                ['Typhoid', 150, 'Negative'],
                ['Malaria', 120, 'Negative'],
                ['HBs', 200, 'Non-reactive'],
                ['HCV', 200, 'Non-reactive'],
                ['HIV', 250, 'Non-reactive'],
                ['VDRL', 150, 'Non-reactive'],
                ['TB-ICT', 250, 'Negative'],
                ['TOXO ICT', 250, 'Negative'],
                ['TORCH Profile ICT', 800, 'See panel'],
                ['TORCH Quantitative', 1200, 'See panel'],
                ['HBV', 250, 'Non-reactive'],
                ['HAV', 250, 'Non-reactive'],
                ['Stool H. pylori Ag Quantitative', 350, 'See kit'],
                ['FOB', 150, 'Negative'],
                ['Pregnancy', 80, 'Negative'],
                ['Brucella', 200, 'Negative'],
                ['Widal', 150, 'See titres'],
                ['CRP', 120, '<5 mg/L'],
                ['RA-Factor', 150, 'Negative'],
                ['ASO', 150, '<200 IU/mL'],
            ]),
            ...self::map(TestDepartment::RoutineChemistry, [
                ['Blood H. pylori Ab', 250, 'Negative'],
                ['HbA1c', 250, '4.0-5.6 %'],
                ['CPK', 200, 'Male 39-308 U/L'],
                ['SGPT', 120, '<41 U/L'],
                ['SGOT', 120, '<40 U/L'],
                ['ALP', 120, '40-129 U/L'],
                ['T-Bilirubin', 100, '0.1-1.2 mg/dL'],
                ['D-Bilirubin', 100, '0.0-0.3 mg/dL'],
                ['I-Bilirubin', 100, '0.1-1.0 mg/dL'],
                ['S. Albumin', 100, '3.5-5.2 g/dL'],
                ['T. protein', 100, '6.4-8.3 g/dL'],
                ['RFT', 280, 'See urea / creatinine'],
                ['Urea', 80, '15-45 mg/dL'],
                ['S. Creatinine', 80, '0.7-1.3 mg/dL'],
                ['BUN', 80, '7-20 mg/dL'],
                ['Electrolyte', 350, 'See Na / K / Cl'],
                ['ABG', 400, 'See arterial gases'],
                ['VBG', 350, 'See venous gases'],
                ['Calcium', 120, '8.6-10.2 mg/dL'],
                ['Amylase', 200, '28-100 U/L'],
                ['Lipase', 250, '13-60 U/L'],
                ['CK-MB', 250, '<25 U/L'],
                ['Lipid profile', 250, 'LDL <100 mg/dL'],
                ['FBS', 80, '70-100 mg/dL'],
                ['RBS', 70, '<140 mg/dL'],
            ]),
            ...self::map(TestDepartment::SpecialChemistry, [
                ['AMH', 900, 'See age chart'],
                ['TFT', 450, 'See T3 / T4 / TSH'],
                ['TSH', 180, '0.4-4.0 mIU/L'],
                ['T3', 180, '1.3-3.1 nmol/L'],
                ['T4', 180, '66-181 nmol/L'],
                ['Vit-D', 350, '30-100 ng/mL'],
                ['Anti-cardiolipin', 600, 'Negative'],
                ['Anti-CCP', 700, '<20 U/mL'],
                ['Testosterone', 500, 'See sex / age'],
                ['Prolactin', 400, 'See sex'],
                ['Troponin-I', 450, '<0.04 ng/mL'],
                ['Troponin-T', 450, '<0.014 ng/mL'],
                ['GH', 500, 'See stimulation'],
                ['D-Dimer', 400, '<500 ng/mL'],
                ['Free T3', 250, '3.1-6.8 pmol/L'],
                ['Free T4', 250, '12-22 pmol/L'],
                ['PSA', 400, '<4.0 ng/mL'],
                ['CA-125', 500, '<35 U/mL'],
                ['LH', 350, 'See cycle / sex'],
                ['FSH', 350, 'See cycle / sex'],
                ['PTH', 500, '15-65 pg/mL'],
                ['Anti-phospholipid', 700, 'Negative'],
                ['Ferritin', 300, 'Male 30-400 ng/mL'],
                ['β-HCG', 350, 'See pregnancy week'],
            ]),
            ...self::map(TestDepartment::Microbiology, [
                ['Urine C/S', 400, 'No growth'],
                ['Stool C/S', 400, 'No pathogen'],
                ['Blood C/S', 600, 'No growth'],
                ['Peritoneal C/S', 500, 'No growth'],
                ['CSF C/S', 600, 'No growth'],
                ['Pleural C/S', 500, 'No growth'],
                ['Ascitic C/S', 500, 'No growth'],
                ['Pus C/S', 400, 'No growth'],
                ['Wound C/S', 400, 'No growth'],
                ['Sputum C/S', 400, 'No growth'],
                ['Vaginal swab C/S', 400, 'No pathogen'],
                ['Throat swab C/S', 350, 'No pathogen'],
                ['Semen C/S', 450, 'No growth'],
                ['AFB 1 sample', 200, 'Negative'],
                ['AFB 3 sample', 500, 'Negative'],
                ['KOH', 150, 'Negative'],
                ['Gram stain', 120, 'See smear'],
                ['HBs PCR', 1500, 'Not detected'],
                ['HCV PCR', 1800, 'Not detected'],
                ['HIV PCR', 1800, 'Not detected'],
            ]),
            ...self::map(TestDepartment::Pathology, [
                ['Small biopsy', 800, 'See histopathology'],
                ['Medium biopsy', 1200, 'See histopathology'],
                ['Large biopsy', 1800, 'See histopathology'],
                ['Blood film', 200, 'See morphology'],
                ['Fluid cytology', 600, 'See cytology'],
            ]),
        ];
    }

    /**
     * @param  list<array{0: string, 1: int|float, 2: string}>  $rows
     * @return list<array{name: string, department: string, price: float, normal_range: string, code: string}>
     */
    private static function map(TestDepartment $department, array $rows): array
    {
        return array_map(function (array $row) use ($department): array {
            [$name, $price, $range] = $row;

            return [
                'name' => $name,
                'department' => $department->value,
                'price' => (float) $price,
                'normal_range' => $range,
                'code' => strtoupper(str_replace([' ', '/', '.', 'β'], ['-', '-', '', 'B'], $name)),
            ];
        }, $rows);
    }
}
