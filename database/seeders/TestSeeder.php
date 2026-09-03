<?php

namespace Database\Seeders;

use App\Enums\TestDepartment;
use App\Models\Test;
use App\Support\LabCatalogue;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (LabCatalogue::tests() as $test) {
            $department = TestDepartment::from($test['department']);

            Test::query()->updateOrCreate(
                ['name' => $test['name']],
                [
                    'code' => $test['code'],
                    'department' => $department,
                    'price' => $test['price'],
                    'normal_range' => $test['normal_range'],
                    'is_active' => $department->isActiveByDefault(),
                ],
            );
        }

        $this->seedTemplates();
    }

    private function seedTemplates(): void
    {
        $fbs = Test::query()->firstWhere('name', 'FBS');
        if ($fbs !== null && $fbs->parameters()->doesntExist()) {
            $fbs->update([
                'interpretation' => 'Fasting blood sugar. Elevated values may indicate diabetes mellitus.',
                'clinical_utility' => 'Screening and monitoring of diabetes.',
                'method' => 'Enzymatic / GOD-POD',
            ]);
            $fbs->parameters()->create([
                'name' => 'B. Sugar (F)',
                'unit' => 'mg/dL',
                'normal_range' => '70-100',
                'sort_order' => 1,
            ]);
        }

        $tb = Test::query()->firstWhere('name', 'TB-ICT');
        if ($tb !== null && $tb->parameters()->doesntExist()) {
            $tb->update([
                'interpretation' => 'TB ICT detects IgG/IgM antibodies. Positive results require clinical correlation.',
                'method' => 'Immunochromatography',
            ]);
            $tb->parameters()->create([
                'name' => 'TB (ICT)',
                'unit' => '',
                'normal_range' => 'Negative',
                'sort_order' => 1,
            ]);
        }

        $tft = Test::query()->firstWhere('name', 'TFT');
        if ($tft !== null && $tft->parameters()->doesntExist()) {
            $tft->update([
                'interpretation' => 'High TSH with low T4 suggests hypothyroidism. Low TSH with high T4 suggests hyperthyroidism.',
                'clinical_utility' => 'Evaluation of thyroid function.',
                'method' => 'Immunoassay',
            ]);
            $tft->parameters()->createMany([
                ['name' => 'T3', 'unit' => 'nmol/L', 'normal_range' => '1.3-3.1', 'sort_order' => 1],
                ['name' => 'T4', 'unit' => 'nmol/L', 'normal_range' => '66-181', 'sort_order' => 2],
                ['name' => 'TSH', 'unit' => 'mIU/L', 'normal_range' => '0.4-4.0', 'sort_order' => 3],
            ]);
        }

        $urine = Test::query()->firstWhere('name', 'Urine R/E');
        if ($urine !== null && $urine->parameters()->doesntExist()) {
            $urine->parameters()->createMany([
                ['name' => 'Color', 'group_name' => 'CHEMICAL EXAMINATION', 'sort_order' => 1],
                ['name' => 'Appearance', 'group_name' => 'CHEMICAL EXAMINATION', 'sort_order' => 2],
                ['name' => 'Protein', 'group_name' => 'CHEMICAL EXAMINATION', 'normal_range' => 'Negative', 'sort_order' => 3],
                ['name' => 'Glucose', 'group_name' => 'CHEMICAL EXAMINATION', 'normal_range' => 'Negative', 'sort_order' => 4],
                ['name' => 'WBC', 'group_name' => 'MICROSCOPIC EXAMINATION', 'normal_range' => '0-5 /HPF', 'sort_order' => 5],
                ['name' => 'RBC', 'group_name' => 'MICROSCOPIC EXAMINATION', 'normal_range' => '0-2 /HPF', 'sort_order' => 6],
            ]);
        }
    }
}
