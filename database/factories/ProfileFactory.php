<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use App\Enums\Profile\ProfileTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        $type = fake()->randomElement(ProfileTypeEnum::cases());
        $isCompany = $type === ProfileTypeEnum::COMPANY;

        return [
            Profile::USER_ID => User::factory(),
            Profile::TYPE => $type,

            Profile::FIRST_NAME => $isCompany ? null : fake()->firstName(),
            Profile::LAST_NAME => $isCompany ? null : fake()->lastName(),

            Profile::COMPANY_NAME => $isCompany ? fake()->company() : null,
            Profile::REG_NUMBER => $isCompany ? (string) fake()->randomNumber(9, true) : null,
            Profile::VAT_NUMBER => $isCompany ? 'LV' . fake()->randomNumber(9, true) : null,

            Profile::CITY => fake()->randomElement([
                'Rīga', 'Daugavpils', 'Jēkabpils', 'Jelgava', 'Jūrmala',
                'Liepāja', 'Rēzekne', 'Valmiera', 'Ventspils', 'Ogre',
                'Tukums', 'Sigulda', 'Cēsis', 'Saldus', 'Dobele',
            ]),
            Profile::PHONE => '+371 ' . fake()->numerify('2#######'),
            Profile::BIO => fake()->realText(200),
            Profile::IS_VERIFIED => fake()->boolean(20),
        ];
    }

    public function forMaster(): static
    {
        return $this->state(function (array $attributes) {
            return [
                Profile::EXPERIENCES     => $this->generateExperiences(),
                Profile::PORTFOLIO_IMAGES => $this->generatePortfolioImages(fake()->numberBetween(3, 6)),
                Profile::IS_VERIFIED     => fake()->boolean(70),
            ];
        });
    }

    private function generateExperiences(): array
    {
        $trades = [
            [
                'title'       => 'Elektriķis',
                'description' => 'Elektroinstalācijas darbi, rozešu un slēdžu montāža, apgaismojuma sistēmas, kabeļu vadi dzīvokļos un privātmājās.',
            ],
            [
                'title'       => 'Santehniķis',
                'description' => 'Cauruļvadu sistēmu izbūve un remonts, krānu un skalotāju nomaiņa, vannas istabas iekārtošana.',
            ],
            [
                'title'       => 'Flīzētājs',
                'description' => 'Keramikas un akmens flīžu likšana virtuvēs, vannas istabās un uz grīdām, izlīdzināšanas darbi.',
            ],
            [
                'title'       => 'Galdnieks',
                'description' => 'Mēbeļu izgatavošana pēc pasūtījuma, restaurācija un atjaunošana, iebūvētās skapju sistēmas.',
            ],
            [
                'title'       => 'Krāsotājs',
                'description' => 'Telpu un fasāžu krāsošana, tapešu līmēšana, virsmu sagatavošana un špaktelēšana.',
            ],
            [
                'title'       => 'Jumtnieks',
                'description' => 'Jumtu segumu montāža un nomaiņa, hidroizolācija, jumta logsienu un drenāžas sistēmu uzstādīšana.',
            ],
            [
                'title'       => 'Metinātājs',
                'description' => 'Metāla konstrukciju metināšana un izgatavošana, nojumes, vārti, margas un metāla kāpnes.',
            ],
            [
                'title'       => 'Apmetējs',
                'description' => 'Iekštelpu un ārsienu apmešana, dekoratīvās apdares darbi, sausā apmetuma sistēmas.',
            ],
            [
                'title'       => 'Grīdas speciālists',
                'description' => 'Lamināta, parketa un koka grīdas ieklāšana, izlīdzināšanas masas lietoša, grīdas siltināšana.',
            ],
            [
                'title'       => 'Logu un durvju montāža',
                'description' => 'PVC un alumīnija logu uzstādīšana un nomaiņa, iekšdurvju un āra durvju montāža, blīvēšana.',
            ],
            [
                'title'       => 'Siltināšanas speciālists',
                'description' => 'Ēku ārsienu siltināšana ar minerālvati un EPS plāksnēm, pēdu sistēmas, bāzes apmetums.',
            ],
            [
                'title'       => 'Ģipškartons',
                'description' => 'Starpsienu izbūve, paneļu montāža, stāvu pārsegumi, uguns un skaņas izolācijas risinājumi.',
            ],
        ];

        $count = fake()->numberBetween(2, 4);
        shuffle($trades);
        $selected = array_slice($trades, 0, $count);

        return array_map(fn($trade) => [
            'title'       => $trade['title'],
            'years'       => (string) fake()->numberBetween(1, 20),
            'description' => $trade['description'],
        ], $selected);
    }

    private function generatePortfolioImages(int $count): array
    {
        $portfolioDir = storage_path('app/public/portfolio');

        if (!is_dir($portfolioDir)) {
            mkdir($portfolioDir, 0755, true);
        }

        $palettes = [
            [[139, 90,  43],  [196, 154, 108]],
            [[43,  90,  139], [108, 154, 196]],
            [[43,  120, 60],  [108, 180, 130]],
            [[139, 43,  60],  [196, 108, 130]],
            [[80,  43,  139], [150, 108, 196]],
            [[120, 110, 40],  [190, 175, 100]],
        ];

        $images = [];

        for ($i = 0; $i < $count; $i++) {
            $filename  = bin2hex(random_bytes(10)) . '.jpg';
            $filepath  = $portfolioDir . '/' . $filename;
            $palette   = $palettes[array_rand($palettes)];
            [$dark, $light] = $palette;

            $img = imagecreatetruecolor(800, 600);

            for ($y = 0; $y < 600; $y++) {
                $t = $y / 600;
                $c = imagecolorallocate(
                    $img,
                    (int) ($dark[0] + ($light[0] - $dark[0]) * $t),
                    (int) ($dark[1] + ($light[1] - $dark[1]) * $t),
                    (int) ($dark[2] + ($light[2] - $dark[2]) * $t),
                );
                imageline($img, 0, $y, 799, $y, $c);
            }

            imagejpeg($img, $filepath, 85);
            imagedestroy($img);

            $images[] = 'portfolio/' . $filename;
        }

        return $images;
    }
}
