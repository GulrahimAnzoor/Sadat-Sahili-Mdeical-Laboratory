<?php

namespace App\Support;

use App\Enums\ReportLayout;
use App\Models\Test;
use Illuminate\Support\Collection;

class LabPanelTemplates
{
    public const Physical = 'PHYSICAL EXAMINATION';

    public const Chemical = 'CHEMICAL EXAMINATION';

    public const Microscopic = 'MICROSCOPIC EXAMINATION';

    public const TorchAntibody = 'IgM';

    public static function urineSummary(): string
    {
        return <<<'TEXT'
Screening test for detection of metabolic abnormalities, liver diseases, biliary and hepatic obstruction, hemolytic diseases and diseases of the kidneys and urinary tract.

Urine composition is affected by three factors:
1. Nutritional status.
2. State of metabolic processes.
3. Ability of the kidney to selectively handle the material presented to it.
TEXT;
    }

    public static function torchSummary(): string
    {
        return <<<'TEXT'
Toxoplasmosis is an infectious disease caused by the parasite Toxoplasma gondii and affects both animals and humans. In humans this infection is usually acquired by ingesting inadequately cooked meat or from feces of infected cats. Approximately 25 to 50% of the adult population are asymptomatically affected with toxoplasmosis. Acquired toxoplasmosis is usually asymptomatic and benign. In pregnant women, however, the infection acquires a special significance as the parasite may enter the fetal circulation through the placenta and cause congenital toxoplasmosis. The consequences of congenital toxoplasmosis range from spontaneous abortion and prematurity to generalized and neurological symptoms. Some infants with congenital toxoplasmosis may also remain asymptomatic at birth and develop the disease during childhood or adolescence.
TEXT;
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function urineParameters(): array
    {
        $rows = [
            ['Volume', null, null, self::Physical],
            ['Color', null, null, self::Physical],
            ['Appearance', null, null, self::Physical],
            ['pH', null, null, self::Physical],
            ['Glucose', null, 'Nil', self::Chemical],
            ['Albumin', null, 'Nil', self::Chemical],
            ['Bilirubin', null, 'Nil', self::Chemical],
            ['Ketone', null, 'Nil', self::Chemical],
            ['Nitrite', null, 'Nil', self::Chemical],
            ['Urobilinogen', null, 'Normal', self::Chemical],
            ['S.G', null, '1.005-1.030', self::Chemical],
            ['Pus cells', '/HPF', '0-10 /HPF', self::Microscopic],
            ['Red cells', '/HPF', '0-1 /HPF', self::Microscopic],
            ['Ep. cells', '/HPF', '0-5 /HPF', self::Microscopic],
            ['Ca oxalate', '/HPF', 'Nil', self::Microscopic],
            ['UA crystal', '/HPF', 'Nil', self::Microscopic],
            ['Mucus', '/HPF', 'Nil', self::Microscopic],
            ['Cysteine crystal', '/HPF', 'Nil', self::Microscopic],
            ['Triple phosphate', '/HPF', 'Nil', self::Microscopic],
            ['A-urate', null, 'Nil', self::Microscopic],
            ['Bacteria', null, 'Nil', self::Microscopic],
            ['Casts', null, 'Not seen', self::Microscopic],
        ];

        return self::mapParameters($rows);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function torchQuantitativeParameters(): array
    {
        $rows = [
            ['Rubella', 'Au/ml', "Non-Reactive: <2.0\nReactive: >2.6"],
            ['TOXO', 'miu/ml', "Non-Reactive: <2.0\nReactive: >2.6"],
            ['CMV', 'Au/ml', "Non-Reactive: <2.0\nReactive: >4.2"],
            ['HSV', 'Au/ml', "Non-Reactive: <2.0\nReactive: >4.0"],
        ];

        return self::mapParameters(array_map(
            fn (array $row): array => [$row[0], $row[1], $row[2], self::TorchAntibody],
            $rows,
        ));
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function torchIctParameters(): array
    {
        $rows = [
            ['Rubella', null, "Non-Reactive\nReactive"],
            ['TOXO', null, "Non-Reactive\nReactive"],
            ['CMV', null, "Non-Reactive\nReactive"],
            ['HSV', null, "Non-Reactive\nReactive"],
        ];

        return self::mapParameters(array_map(
            fn (array $row): array => [$row[0], $row[1], $row[2], self::TorchAntibody],
            $rows,
        ));
    }

    /**
     * @return list<array{name: string, layout: ReportLayout, interpretation: string, parameters: list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>, activate: bool}>
     */
    public static function panels(): array
    {
        return [
            [
                'name' => 'Urine R/E',
                'layout' => ReportLayout::UrineExam,
                'interpretation' => self::urineSummary(),
                'parameters' => self::urineParameters(),
                'activate' => false,
            ],
            [
                'name' => 'TORCH Quantitative',
                'layout' => ReportLayout::TorchPanel,
                'interpretation' => self::torchSummary(),
                'parameters' => self::torchQuantitativeParameters(),
                'activate' => false,
            ],
            [
                'name' => 'TORCH Profile ICT',
                'layout' => ReportLayout::TorchPanel,
                'interpretation' => self::torchSummary(),
                'parameters' => self::torchIctParameters(),
                'activate' => false,
            ],
            [
                'name' => 'CBC',
                'layout' => ReportLayout::Panel,
                'interpretation' => self::cbcSummary(),
                'parameters' => self::cbcParameters(),
                'activate' => false,
            ],
            [
                'name' => 'Brucella',
                'layout' => ReportLayout::Panel,
                'interpretation' => self::brucellaSummary(),
                'parameters' => self::brucellaParameters(),
                'activate' => false,
            ],
            [
                'name' => 'Stool R/E',
                'layout' => ReportLayout::StoolExam,
                'interpretation' => self::stoolSummary(),
                'parameters' => self::stoolParameters(),
                'activate' => false,
            ],
            [
                'name' => 'TOXO ICT',
                'layout' => ReportLayout::Panel,
                'interpretation' => self::torchSummary(),
                'parameters' => self::toxoParameters(),
                'activate' => false,
            ],
            [
                'name' => 'Semen analysis',
                'layout' => ReportLayout::Panel,
                'interpretation' => self::semenSummary(),
                'parameters' => self::semenParameters(),
                'activate' => false,
            ],
            [
                'name' => 'Typhoid',
                'layout' => ReportLayout::Panel,
                'interpretation' => self::typhoidSummary(),
                'parameters' => self::typhoidParameters(),
                'activate' => false,
            ],
            [
                'name' => 'Widal',
                'layout' => ReportLayout::Panel,
                'interpretation' => self::widalSummary(),
                'parameters' => self::widalParameters(),
                'activate' => false,
            ],
            [
                'name' => 'Urine C/S',
                'layout' => ReportLayout::Culture,
                'interpretation' => self::cultureSummary(),
                'parameters' => self::cultureParameters(),
                'activate' => true,
            ],
            [
                'name' => 'Stool C/S',
                'layout' => ReportLayout::Culture,
                'interpretation' => self::cultureSummary(),
                'parameters' => self::cultureParameters(),
                'activate' => true,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function antibioticsFor(string $testName): array
    {
        return match ($testName) {
            'Stool C/S' => [
                'Ampicillin',
                'Amoxicillin-Clavulanate',
                'Ceftriaxone',
                'Ciprofloxacin',
                'Levofloxacin',
                'Co-trimoxazole',
                'Gentamicin',
                'Amikacin',
                'Azithromycin',
                'Chloramphenicol',
                'Imipenem',
                'Nalidixic acid',
            ],
            default => [
                'Ampicillin',
                'Amoxicillin-Clavulanate',
                'Ceftriaxone',
                'Cefixime',
                'Ciprofloxacin',
                'Levofloxacin',
                'Nitrofurantoin',
                'Co-trimoxazole',
                'Gentamicin',
                'Amikacin',
                'Imipenem',
                'Nalidixic acid',
            ],
        };
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function cbcParameters(): array
    {
        return self::mapParameters([
            ['HB', 'g/dL', "Male 13-17\nFemale 12-15", 'HAEMATOLOGY'],
            ['RBC', 'x10^6/µL', '4.5-5.5', 'HAEMATOLOGY'],
            ['HCT', '%', "Male 40-50\nFemale 36-46", 'HAEMATOLOGY'],
            ['MCV', 'fL', '80-96', 'HAEMATOLOGY'],
            ['MCH', 'pg', '27-32', 'HAEMATOLOGY'],
            ['MCHC', 'g/dL', '32-36', 'HAEMATOLOGY'],
            ['TLC', 'x10^3/µL', '4.0-11.0', 'HAEMATOLOGY'],
            ['PLT', 'x10^3/µL', '150-450', 'HAEMATOLOGY'],
            ['Neutrophils', '%', '40-75', 'DIFFERENTIAL COUNT'],
            ['Lymphocytes', '%', '20-45', 'DIFFERENTIAL COUNT'],
            ['Monocytes', '%', '2-10', 'DIFFERENTIAL COUNT'],
            ['Eosinophils', '%', '1-6', 'DIFFERENTIAL COUNT'],
            ['Basophils', '%', '0-1', 'DIFFERENTIAL COUNT'],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function brucellaParameters(): array
    {
        return self::mapParameters([
            ['B. abortus', 'titre', 'Negative <1:80', 'BRUCELLA'],
            ['B. melitensis', 'titre', 'Negative <1:80', 'BRUCELLA'],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function stoolParameters(): array
    {
        return self::mapParameters([
            ['Color', null, null, self::Physical],
            ['Consistency', null, null, self::Physical],
            ['Mucus', null, 'Nil', self::Physical],
            ['Blood', null, 'Nil', self::Physical],
            ['Occult blood', null, 'Negative', self::Chemical],
            ['Reducing sugar', null, 'Negative', self::Chemical],
            ['pH', null, '6.0-7.5', self::Chemical],
            ['Pus cells', '/HPF', '0-2 /HPF', self::Microscopic],
            ['Red cells', '/HPF', 'Nil', self::Microscopic],
            ['Fat globules', null, 'Nil', self::Microscopic],
            ['Starch', null, 'Nil', self::Microscopic],
            ['Muscle fibers', null, 'Nil', self::Microscopic],
            ['Ova', null, 'Not seen', self::Microscopic],
            ['Cyst', null, 'Not seen', self::Microscopic],
            ['Trophozoite', null, 'Not seen', self::Microscopic],
            ['Bacteria', null, 'Normal flora', self::Microscopic],
            ['Yeast', null, 'Nil', self::Microscopic],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function toxoParameters(): array
    {
        return self::mapParameters([
            ['TOXO IgG', null, 'Non-Reactive', 'TOXO'],
            ['TOXO IgM', null, 'Non-Reactive', 'TOXO'],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function semenParameters(): array
    {
        return self::mapParameters([
            ['Volume', 'ml', '1.5-6.0', self::Physical],
            ['Color', null, 'Grey-white', self::Physical],
            ['Liquefaction', 'min', '20-30', self::Physical],
            ['Viscosity', null, 'Normal', self::Physical],
            ['pH', null, '7.2-8.0', self::Physical],
            ['Sperm count', 'million/ml', '≥15', self::Microscopic],
            ['Total motility', '%', '≥40', self::Microscopic],
            ['Progressive motility', '%', '≥32', self::Microscopic],
            ['Morphology', '%', '≥4 normal forms', self::Microscopic],
            ['WBC', 'million/ml', '<1', self::Microscopic],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function typhoidParameters(): array
    {
        return self::mapParameters([
            ['Typhoid IgG', null, 'Non-Reactive', 'TYPHOID ICT'],
            ['Typhoid IgM', null, 'Non-Reactive', 'TYPHOID ICT'],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function widalParameters(): array
    {
        return self::mapParameters([
            ['S. typhi O (TO)', 'titre', '<1:80', 'WIDAL TITRES'],
            ['S. typhi H (TH)', 'titre', '<1:80', 'WIDAL TITRES'],
            ['S. paratyphi AO', 'titre', '<1:80', 'WIDAL TITRES'],
            ['S. paratyphi AH', 'titre', '<1:80', 'WIDAL TITRES'],
            ['S. paratyphi BO', 'titre', '<1:80', 'WIDAL TITRES'],
            ['S. paratyphi BH', 'titre', '<1:80', 'WIDAL TITRES'],
        ]);
    }

    /**
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    public static function cultureParameters(): array
    {
        return self::mapParameters([
            ['Specimen', null, null, 'CULTURE'],
            ['Growth', null, 'No growth', 'CULTURE'],
            ['Colony count', 'CFU/ml', null, 'CULTURE'],
            ['Gram stain', null, null, 'CULTURE'],
            ['Organism', null, null, 'CULTURE'],
        ]);
    }

    public static function cbcSummary(): string
    {
        return 'Complete blood count evaluates red cells, white cells and platelets. Correlate with clinical findings and, when indicated, a peripheral smear.';
    }

    public static function brucellaSummary(): string
    {
        return 'Brucella agglutination titres of 1:80 or greater are significant when correlated with clinical findings. A rising titre on paired sera is more informative than a single result.';
    }

    public static function stoolSummary(): string
    {
        return 'Stool routine examination screens for parasitic infestation, inflammation, malabsorption and occult bleeding. Correlate microscopy with clinical history.';
    }

    public static function semenSummary(): string
    {
        return 'Semen analysis follows WHO reference values. Interpret fertility potential together with clinical history, abstinence period and repeat testing when values are borderline.';
    }

    public static function typhoidSummary(): string
    {
        return 'Typhoid ICT detects IgG and IgM antibodies to Salmonella typhi. IgM suggests recent or acute infection; IgG may indicate past or later-stage infection. Confirm with culture when possible.';
    }

    public static function widalSummary(): string
    {
        return 'Widal titres below 1:80 are usually not significant. A four-fold rise on paired sera, or a high titre with compatible clinical findings, supports the diagnosis of enteric fever.';
    }

    public static function cultureSummary(): string
    {
        return 'Culture isolates the organism and an antibiotic sensitivity list guides treatment. No growth does not exclude infection when the patient is already on antibiotics.';
    }

    /**
     * @return list<string>
     */
    public static function officialNames(string $testName): array
    {
        foreach (self::panels() as $panel) {
            if ($panel['name'] === $testName) {
                return array_column($panel['parameters'], 'name');
            }
        }

        return [];
    }

    /**
     * @param  iterable<int, array{name?: mixed, value?: mixed, unit?: mixed, normal_range?: mixed, group_name?: mixed}>  $rows
     * @return Collection<int, array{name: string, value: mixed, unit: mixed, normal_range: mixed, group_name: mixed}>
     */
    public static function visibleEntryRows(iterable $rows, string $testName = ''): Collection
    {
        $official = $testName !== '' ? self::officialNames($testName) : [];

        return collect($rows)
            ->filter(function (mixed $row) use ($official): bool {
                if (! is_array($row)) {
                    return false;
                }

                $name = trim((string) ($row['name'] ?? ''));

                if ($name === '') {
                    return false;
                }

                if ($official === []) {
                    return true;
                }

                return in_array($name, $official, true) || filled($row['value'] ?? null);
            })
            ->values();
    }

    /**
     * @param  list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>  $parameters
     */
    public static function sync(Test $test, ReportLayout $layout, string $interpretation, array $parameters, bool $activate = false): void
    {
        $payload = [
            'report_layout' => $layout,
            'interpretation' => $interpretation,
        ];

        if ($activate) {
            $payload['is_active'] = true;
        }

        $test->update($payload);

        $keep = [];

        foreach ($parameters as $parameter) {
            $keep[] = $parameter['name'];

            $test->parameters()->updateOrCreate(
                ['name' => $parameter['name']],
                [
                    'unit' => $parameter['unit'],
                    'normal_range' => $parameter['normal_range'],
                    'group_name' => $parameter['group_name'],
                    'sort_order' => $parameter['sort_order'],
                ],
            );
        }

        $test->parameters()
            ->whereNotIn('name', $keep)
            ->whereDoesntHave('resultValues')
            ->delete();
    }

    /**
     * @param  list<array{0: string, 1: ?string, 2: ?string, 3: string}>  $rows
     * @return list<array{name: string, unit: ?string, normal_range: ?string, group_name: string, sort_order: int}>
     */
    private static function mapParameters(array $rows): array
    {
        return array_map(function (array $row, int $index): array {
            return [
                'name' => $row[0],
                'unit' => $row[1],
                'normal_range' => $row[2],
                'group_name' => $row[3],
                'sort_order' => $index + 1,
            ];
        }, $rows, array_keys($rows));
    }
}
