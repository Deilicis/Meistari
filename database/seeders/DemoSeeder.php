<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategorySuggestionStatusEnum;
use App\Enums\EscrowStatusEnum;
use App\Enums\Job\ApplicationStatusEnum;
use App\Enums\Job\JobStatusEnum;
use App\Enums\MessageTypeEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\PriceProposalStatusEnum;
use App\Enums\Profile\ProfileTypeEnum;
use App\Enums\Role\RoleNameEnum;
use App\Models\Application;
use App\Models\Category;
use App\Models\CategorySuggestion;
use App\Models\Conversation;
use App\Models\EscrowHold;
use App\Models\JobDispute;
use App\Models\JobRequest;
use App\Models\Message;
use App\Models\Notification;
use App\Models\PriceProposal;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    private function getPassword(): string
    {
        return env('DEMO_USER_PASSWORD', 'Password1!');
    }

    private User $admin;
    /** @var array<string, User> */
    private array $masters = [];
    /** @var array<string, User> */
    private array $seekers = [];
    /** @var array<string, Category> */
    private array $cats = [];

    public function run(): void
    {
        $this->cleanUp();
        $this->createUsers();
        $this->loadCategories();
        $this->createServices();
        $this->createJobsAndApplications();
        $this->createCategorySuggestions();
        $this->createNotifications();

        Carbon::setTestNow(null);
    }

    private function cleanUp(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_user')->truncate();
        foreach ([
            Notification::class, JobDispute::class, EscrowHold::class,
            Review::class, PriceProposal::class, Application::class,
            ServiceApplication::class, CategorySuggestion::class,
            Message::class, Conversation::class, Service::class,
            JobRequest::class, Profile::class, User::class,
        ] as $model) {
            $model::truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    // --- Lietotāji ---

    private function createUsers(): void
    {
        $hashed = Hash::make($this->getPassword());

        $adminRole = Role::where(Role::NAME, RoleNameEnum::ADMIN->value)->firstOrFail();
        $masterRole = Role::where(Role::NAME, RoleNameEnum::MASTER->value)->firstOrFail();
        $seekerRole = Role::where(Role::NAME, RoleNameEnum::SEEKER->value)->firstOrFail();

        // Administrators
        $this->admin = User::create([
            User::NAME => 'Demo Admins',
            User::EMAIL => 'admin@meistari.local',
            User::PASSWORD => $hashed,
            User::EMAIL_VERIFIED_AT => now(),
            User::ACTIVE_ROLE => RoleNameEnum::ADMIN,
        ]);
        $this->admin->roles()->attach($adminRole->getId());
        $this->makeProfile($this->admin, 'Demo', 'Admins', 'Rīga', '+371 29000000',
            'Platformas administrators.', true, []);

        // Meistari
        foreach ($this->mastersData() as $key => $d) {
            Carbon::setTestNow(null);
            $user = User::create([
                User::NAME => $d['name'],
                User::EMAIL => "{$key}@meistari.local",
                User::PASSWORD => $hashed,
                User::EMAIL_VERIFIED_AT => now(),
                User::ACTIVE_ROLE => RoleNameEnum::MASTER,
            ]);
            $user->roles()->attach($masterRole->getId());
            [$first, $last] = explode(' ', $d['name'], 2);
            $this->makeProfile($user, $first, $last, $d['city'], $d['phone'],
                $d['bio'], true, $d['exp']);
            $this->masters[$key] = $user;
        }

        // Jānis - abas lomas
        $janis = User::create([
            User::NAME => 'Jānis Auziņš',
            User::EMAIL => 'janis@meistari.local',
            User::PASSWORD => $hashed,
            User::EMAIL_VERIFIED_AT => now(),
            User::ACTIVE_ROLE => RoleNameEnum::MASTER,
        ]);
        $janis->roles()->attach($masterRole->getId());
        $janis->roles()->attach($seekerRole->getId());
        $this->makeProfile($janis, 'Jānis', 'Auziņš', 'Rīga', '+371 26543210',
            'Flīzētājs ar 9 gadu pieredzi. Paralēli esmu arī aktīvs pakalpojumu meklētājs.',
            true,
            [['title' => 'Flīzētājs', 'years' => '9',
              'description' => 'Keramikas un akmens flīžu darbi virtuvēs, vannas istabās un gaitenī.']]);
        $this->masters['janis'] = $janis;
        $this->seekers['janis'] = $janis;

        // Darba meklētāji
        foreach ($this->seekersData() as $key => $d) {
            $user = User::create([
                User::NAME => $d['name'],
                User::EMAIL => "{$key}@meistari.local",
                User::PASSWORD => $hashed,
                User::EMAIL_VERIFIED_AT => now(),
                User::ACTIVE_ROLE => RoleNameEnum::SEEKER,
            ]);
            $user->roles()->attach($seekerRole->getId());
            [$first, $last] = explode(' ', $d['name'], 2);
            $this->makeProfile($user, $first, $last, $d['city'], $d['phone'], null, false, []);
            $this->seekers[$key] = $user;
        }
    }

    private function makeProfile(User $user, string $first, string $last, string $city,
        string $phone, ?string $bio, bool $verified, array $exp): void
    {
        Profile::create([
            Profile::USER_ID => $user->getId(),
            Profile::TYPE => ProfileTypeEnum::INDIVIDUAL,
            Profile::FIRST_NAME => $first,
            Profile::LAST_NAME => $last,
            Profile::CITY => $city,
            Profile::PHONE => $phone,
            Profile::BIO => $bio,
            Profile::IS_VERIFIED => $verified,
            Profile::EXPERIENCES => $exp,
            Profile::PORTFOLIO_IMAGES => [],
        ]);
    }

    private function mastersData(): array
    {
        return [
            'andrejs' => [
                'name' => 'Andrejs Bērziņš', 'city' => 'Rīga', 'phone' => '+371 29111111',
                'bio' => 'Sertificēts elektriķis ar 12 gadu pieredzi. Veicu dzīvokļu, privātmāju un komercobjektu elektroinstalāciju, rozešu un slēdžu montāžu, apgaismojuma sistēmu uzstādīšanu.',
                'exp' => [['title' => 'Elektriķis', 'years' => '12',
                            'description' => 'Elektroinstalācija dzīvokļos, privātmājās un komercobjektos. Rozetes, slēdži, apgaismojums, drošinātāji.']],
            ],
            'peteris' => [
                'name' => 'Pēteris Kalniņš', 'city' => 'Daugavpils', 'phone' => '+371 29222222',
                'bio' => 'Santehniķis ar 8 gadu pieredzi. Specializējos cauruļvadu sistēmās, vannas istabu iekārtošanā un ūdens sildītāju uzstādīšanā.',
                'exp' => [['title' => 'Santehniķis', 'years' => '8',
                            'description' => 'Cauruļvadi, sanitārtehnika, siltais grīdas, ūdens sildītāji.']],
            ],
            'martins' => [
                'name' => 'Mārtiņš Ozols', 'city' => 'Jelgava', 'phone' => '+371 29333333',
                'bio' => 'Profesionāls krāsotājs ar 10 gadu pieredzi gan iekštelpu, gan fasāžu krāsošanā. Strādāju ar augstvērtīgiem krāsu zīmoliem.',
                'exp' => [['title' => 'Krāsotājs', 'years' => '10',
                            'description' => 'Iekštelpu un fasāžu krāsošana, špaktelēšana, tapešu līmēšana.']],
            ],
            'ilze' => [
                'name' => 'Ilze Liepiņa', 'city' => 'Liepāja', 'phone' => '+371 29444444',
                'bio' => 'Profesionāla uzkopšanas pakalpojumu sniedzēja. Strādāju ar privātiem un komerciāliem klientiem jau 6 gadus. Liela uzmanība detaļām.',
                'exp' => [['title' => 'Tīrīšanas speciāliste', 'years' => '6',
                            'description' => 'Dzīvokļi, biroji, pēcremonta tīrīšana.']],
            ],
            'roberts' => [
                'name' => 'Roberts Krūmiņš', 'city' => 'Valmiera', 'phone' => '+371 29555555',
                'bio' => 'Galdnieks un būvnieks ar 15 gadu pieredzi. Izgatavoju mēbeles pēc pasūtījuma, uzstādu durvis un žogus, veicu kokapstrādes darbus.',
                'exp' => [['title' => 'Galdnieks', 'years' => '15',
                            'description' => 'Mēbeļu izgatavošana, koka durvju montāža, žogi un terase.']],
            ],
            'linda' => [
                'name' => 'Linda Vītola', 'city' => 'Jūrmala', 'phone' => '+371 29666666',
                'bio' => 'Dārzkopības speciāliste ar 7 gadu pieredzi. Palīdzu veidot un uzturēt skaistus dārzus, veicu zāles pļaušanu, stādīšanu un sniega tīrīšanu.',
                'exp' => [['title' => 'Dārzkopja', 'years' => '7',
                            'description' => 'Dārza kopšana un stādīšana, zāliena aprūpe, sniega tīrīšana.']],
            ],
            'kristine' => [
                'name' => 'Kristīne Saulīte', 'city' => 'Rīga', 'phone' => '+371 29777777',
                'bio' => 'Profesionāla logu mazgātāja ar alpīnisma sertifikātu. Strādāju ar jebkura augstuma ēkām - no privātmājām līdz daudzstāvu birojiem.',
                'exp' => [['title' => 'Logu tīrīšanas speciāliste', 'years' => '5',
                            'description' => 'Logu un karkasu mazgāšana daudzdzīvokļu mājās un privātmājās.']],
            ],
        ];
    }

    private function seekersData(): array
    {
        return [
            'tonijs' => ['name' => 'Tonijs Bāliņš',  'city' => 'Rīga',       'phone' => '+371 28111111'],
            'anna' => ['name' => 'Anna Riekstiņa',  'city' => 'Jūrmala',    'phone' => '+371 28222222'],
            'karlis' => ['name' => 'Kārlis Dārziņš',  'city' => 'Liepāja',    'phone' => '+371 28333333'],
            'madara' => ['name' => 'Madara Egle',      'city' => 'Valmiera',   'phone' => '+371 28444444'],
            'gunars' => ['name' => 'Gunārs Bērziņš',  'city' => 'Daugavpils', 'phone' => '+371 28555555'],
        ];
    }

    // --- Kategorijas ---

    private function loadCategories(): void
    {
        Category::all()->each(function (Category $c) {
            $this->cats[$c->getName()] = $c;
        });
    }

    private function c(string $name): Category
    {
        return $this->cats[$name]
            ?? throw new \RuntimeException("Category not found: {$name}");
    }

    // --- Pakalpojumi ---

    private function createServices(): void
    {
        Carbon::setTestNow(Carbon::now()->subDays(60));

        $services = [
            // Andrejs - elektriķis
            ['andrejs', 'Elektroinstalācijas darbi dzīvokļos', 'Iekšējie darbi', 350.00,
             'Veicu pilnu dzīvokļu elektroinstalāciju - rozešu, slēdžu montāža, elektrisko sadaļu uzstādīšana, vadu vads grīdā un sienās. Garantija 2 gadi. Darbus veicu rūpīgi un tīri.'],
            ['andrejs', 'Apgaismojuma montāža un konfigurācija', 'Apgaismojums', 25.00,
             'Lustru, LED lentes, sienas gaismekļu un downlight montāža. Palīdzu izvēlēties pareizo gaismas temperatūru un izvietojumu. Pieejams arī viedās mājas sistēmu integrācijai.'],
            ['andrejs', 'Ārējā elektroinstalācija privātmājām', 'Ārējie darbi', 800.00,
             'Pieslēgšanās tīklam, āra apgaismojums, dārza rozetes, drošinātāju kastes montāža ārpusē. Strādāju ar sertificētiem materiāliem.'],

            // Pēteris - santehniķis
            ['peteris', 'Cauruļvadu uzstādīšana un remonts', 'Cauruļvadi', 30.00,
             'Ūdens un kanalizācijas cauruļvadu montāža, noplūžu novēršana, caurules nomaiņa. Ātri reaģēju avārijas gadījumos. Strādāju ar PPR, vara un plastmasas caurulēm.'],
            ['peteris', 'Sanitārtehnikas montāža', 'Sanitārtehnika', 200.00,
             'Vanniņas, dušas kabīnes, tualetes, izlietnes uzstādīšana un pieslēgšana. Varu nodrošināt arī ūdens mīkstinātāju uzstādīšanu. Garantija 1 gads.'],
            ['peteris', 'Ūdens sildītāja uzstādīšana', 'Ūdens sildītāji', 120.00,
             'Dažādu ražotāju ūdens sildītāju uzstādīšana un pieslēgšana. Nodrošinu arī vecā sildītāja demontāžu un utilizāciju. Darbs ar garantiju.'],

            // Mārtiņš - krāsotājs
            ['martins', 'Dzīvokļu iekštelpu krāsošana', 'Iekšējā krāsošana', 400.00,
             'Sienu un griestu krāsošana ar kvalitatīvām materiālām. Ietver virsmas sagatavošanu, špaktelēšanu un gruntēšanu. Darbu veicu ātri un tīri, aizsargājot mēbeles.'],
            ['martins', 'Fasāžu krāsošana un renovācija', 'Fasāžu krāsošana', 15.00,
             'Ēku fasāžu krāsošana ar speciāliem āra krāsojumiem. Nodrošinu sastatnes un visu nepieciešamo aprīkojumu. Darbu var veikt gan mazām, gan lielām ēkām.'],

            // Ilze - uzkopšana
            ['ilze', 'Pēcremonta tīrīšana dzīvokļos', 'Pēcremonta tīrīšana', 180.00,
             'Rūpīga telpu uzkopšana pēc remonta darbiem - putekļu, apmetuma un krāsas palieku savākšana, logu, durvju un grīdu mazgāšana. Izmantoju profesionālu aprīkojumu.'],
            ['ilze', 'Biroju regulārā tīrīšana', 'Biroju tīrīšana', 15.00,
             'Biroja telpu regulāra ikdienas vai iknedēļas uzkopšana. Putekļu slaucīšana, grīdu mazgāšana, virtuves zona, sanitārie mezgli. Diskrēti un uzticami.'],
            ['ilze', 'Dzīvokļa ģenerāltīrīšana', 'Dzīvokļu uzkopšana', 130.00,
             'Pilna dzīvokļa ģenerāltīrīšana - ledusskapja mazgāšana iekšā, mikroviļņu krāsns, virtuves virsmas, vannas istaba, logu stikli, grīdas. Viss blizgst.'],

            // Roberts - galdniecība
            ['roberts', 'Mēbeļu izgatavošana pēc pasūtījuma', 'Iekšējais remonts', null,
             'Dizaina mēbeles pēc individuāla pasūtījuma - skapji, plaukti, virtuves mēbeles, galdi un krēsli. Strādāju ar dažādiem koku veidiem. Iespēja sniegt tāmi pēc rasējuma.'],
            ['roberts', 'Koka žogu izbūve', 'Ārējais remonts', 55.00,
             'Dažāda veida koka žogu projektēšana un izbūve. Lietoju impregnētu koku vai teraskoku. Ietver pamatu vai balstu uzstādīšanu. Darbu veicu visu gadu.'],
            ['roberts', 'Iekšdurvju uzstādīšana', 'Iekšējais remonts', 160.00,
             'Iekštelpu durvju montāža ar visu furnitūru - eņģes, slēdzenes, sliekšņi. Strādāju arī ar čuguna durvīm. Precīzs mērījums un perfekta montāža.'],

            // Linda - dārzkopība
            ['linda', 'Dārza kopšana un zāles pļaušana', 'Zāles pļaušana', 20.00,
             'Regulāra dārza kopšana un zāliena pļaušana ar profesionālu aprīkojumu. Nodrošinu arī zāles savākšanu un utilizāciju. Pieejama regulāra apkalpošana visas vasaras garumā.'],
            ['linda', 'Dārza stādīšana un labiekārtošana', 'Stādīšana', 280.00,
             'Dārza projektēšana un stādīšana - augļu koki, krūmi, daudzgadīgie augi, dzīvžogi. Palīdzu izvēlēties augus atbilstoši apgaismojumam un augsnei. Bezmaksas konsultācija.'],
            ['linda', 'Sniega tīrīšana privātīpašumā', 'Sniega tīrīšana', 18.00,
             'Piebraucamā ceļa, dārza taciņu un terases tīrīšana no sniega. Arī apkaisīšana ar smiltīm/sāli. Ātrs atsaucības laiks - būšu pie jums 2 stundu laikā.'],

            // Jānis - flīzēšana
            ['janis', 'Flīžu klāšana virtuvēs un vannas istabās', 'Iekšējais remonts', 25.00,
             'Keramikas, akmens un mozaīkas flīžu klāšana. Precīzs darbs ar minimālu šuvju platumu. Iekļauj pamatnes sagatavošanu un hidroizolāciju. Liela pieredze sudrabainā un sarežģītā zīmējumā.'],
            ['janis', 'Grīdas flīžu klāšana lielās platībās', 'Iekšējais remonts', 18.00,
             'Lielu laukumu flīzēšana - priekšnami, gaiteņi, terases. Darbu veicu ātri un precīzi. Varu strādāt gan ar pieejamiem materiāliem, gan palīdzēt ar materiālu izvēli.'],

            // Kristīne - logu mazgāšana
            ['kristine', 'Logu mazgāšana daudzdzīvokļu mājām', 'Stiklu pakalpojumi', 3.00,
             'Logu ārpuses un iekšpuses mazgāšana dzīvokļos un daudzdzīvokļu mājās. Cena norādīta par vienu logu vērtni. Izmantoju profesionālos tīrīšanas līdzekļus bez strīpām.'],
            ['kristine', 'Karkasu un rāmju mazgāšana', 'Karkasu mazgāšana', 15.00,
             'Logu rāmju, karkasu un palodžu rūpīga tīrīšana. Noņemu seno krāsu, netīrumus un pelējumu. Strādāju arī ar PVC un alumīnija rāmjiem.'],
            ['kristine', 'Logu tīrīšana privātmājām', 'Stiklu pakalpojumi', 80.00,
             'Privātmājas logu pilnīga tīrīšana iekšpusē un ārpusē. Ietver palodzes, karkasus un žalūzijas. Cena atkarīga no logu skaita - sazinies, lai saņemtu individuālu piedāvājumu.'],
        ];

        foreach ($services as $i => [$masterKey, $title, $subCatName, $price, $desc]) {
            Service::create([
                Service::USER_ID => $this->masters[$masterKey]->getId(),
                Service::CATEGORY_ID => $this->c($subCatName)->getId(),
                Service::TITLE => $title,
                Service::SLUG => Str::slug($title) . '-' . ($i + 1),
                Service::DESCRIPTION => $desc,
                Service::PRICE => $price,
                Service::LOCATION => [$this->masters[$masterKey]->profile->getCity()],
                Service::IS_ACTIVE => true,
            ]);
        }

        // Daži pakalpojumu pieteikumi
        Carbon::setTestNow(Carbon::now()->subDays(10));
        $andrejs1 = Service::where(Service::USER_ID, $this->masters['andrejs']->getId())->first();
        if ($andrejs1) {
            ServiceApplication::create([
                ServiceApplication::SERVICE_ID => $andrejs1->getId(),
                ServiceApplication::USER_ID => $this->seekers['tonijs']->getId(),
                ServiceApplication::MESSAGE => 'Sveiki! Interesē apgaismojuma montāža dzīvokļa viesistabā. Vai varat sniegt tāmi?',
                ServiceApplication::BUDGET_OFFER => 200.00,
                ServiceApplication::STATUS => ApplicationStatusEnum::PENDING,
            ]);
        }
        $ilze3 = Service::where(Service::USER_ID, $this->masters['ilze']->getId())
            ->orderBy(Service::ID, 'desc')->first();
        if ($ilze3) {
            ServiceApplication::create([
                ServiceApplication::SERVICE_ID => $ilze3->getId(),
                ServiceApplication::USER_ID => $this->seekers['karlis']->getId(),
                ServiceApplication::MESSAGE => 'Nepieciešama ģenerāltīrīšana pirms īres līguma beigām.',
                ServiceApplication::BUDGET_OFFER => 130.00,
                ServiceApplication::STATUS => ApplicationStatusEnum::ACCEPTED,
            ]);
        }

        Carbon::setTestNow(null);
    }

    // --- Darbi, Pieteikumi, Piedāvājumi, Depozīts ---

    private function createJobsAndApplications(): void
    {
        $m = $this->masters;
        $s = $this->seekers;

        // --- TONIJS (5 darba sludinājumi) ---

        // 1. OPEN - andrejs
        Carbon::setTestNow(Carbon::now()->subDays(4));
        $j1 = $this->makeJob($s['tonijs'], 'Elektroinstalācija viesistabā', $this->c('Iekšējie darbi'), 300.00, ['Rīga'],
            'Nepieciešams uzstādīt 4 jaunas rozetes viesistabā un nomainīt galveno sadalnī paneļa drošinātāju. Dzīvoklis 3. stāvā. Labprāt sazinātos šonedēļ.');

        Carbon::setTestNow(Carbon::now()->subDays(3)->addHours(2));
        $a1_andrejs = $this->makeApp($j1, $m['andrejs'], ApplicationStatusEnum::SHORTLISTED, 280.00);
        $p1 = $this->makeProposal($a1_andrejs, $m['andrejs'], PriceProposalStatusEnum::COUNTERED, 280.00, null,
            Carbon::now()->subDays(2));
        $this->makeProposal($a1_andrejs, $s['tonijs'], PriceProposalStatusEnum::COUNTERED, 240.00, 'Vai iespējams par šo cenu, ja darbus veicam šajā mēnesī?',
            Carbon::now()->subDays(1), $p1);
        $p3 = $this->makeProposal($a1_andrejs, $m['andrejs'], PriceProposalStatusEnum::PENDING, 260.00,
            'Šajā cenā iekļauti visi materiāli. Varu sākt jau rītdien.',
            Carbon::now()->subHours(5), null);
        $conv1 = $this->makeConversation($s['tonijs'], $m['andrejs']);
        $this->addMessages($conv1, $s['tonijs'], $m['andrejs'], [
            [$s['tonijs'], 'Sveiki! Redzēju jūsu profilu - izskatās profesionāli.', -72],
            [$m['andrejs'], 'Labdien! Paldies. Ko vēlaties paveikt?', -71],
            [$s['tonijs'], 'Man vajag 4 rozetes viesistabā un drošinātāja nomaiņu.', -70],
            [$m['andrejs'], 'Sapratu. Vai ir pieejama plāna zīmēšana vai varu apskatīt uz vietas?', -48],
            [$s['tonijs'], 'Varat apskatīt - sazināsimies par laiku.', -47],
            [$m['andrejs'], 'Lieliski. Esmu nosūtījis piedāvājumu.', -46],
            [$s['tonijs'], 'Cena ir nedaudz augsta, vai varam vienoties par 240?', -24],
            [$m['andrejs'], 'Nosūtīju jaunu piedāvājumu - 260 ar visiem materiāliem.', -5, true],
        ]);

        // 2. ACCEPTED - peteris
        Carbon::setTestNow(Carbon::now()->subDays(12));
        $j2 = $this->makeJob($s['tonijs'], 'Kanalizācijas caurules noplūde virtuvē', $this->c('Cauruļvadi'), 250.00, ['Rīga'],
            'Zem virtuves izlietnes plūst ūdens - droši vien uzgale. Vajag ātru remontu. Dzīvoklis Purvciemā.');
        Carbon::setTestNow(Carbon::now()->subDays(11));
        $a2_peteris = $this->makeApp($j2, $m['peteris'], ApplicationStatusEnum::ACCEPTED, 350.00);
        $this->makeApp($j2, $m['andrejs'], ApplicationStatusEnum::REJECTED, 380.00);
        $pp2 = $this->makeProposal($a2_peteris, $m['peteris'], PriceProposalStatusEnum::COUNTERED, 380.00, null,
            Carbon::now()->subDays(10));
        $pp2b = $this->makeProposal($a2_peteris, $s['tonijs'], PriceProposalStatusEnum::COUNTERED, 320.00, null,
            Carbon::now()->subDays(9), $pp2);
        $pp2c = $this->makeProposal($a2_peteris, $m['peteris'], PriceProposalStatusEnum::ACCEPTED, 350.00,
            'Labi, 350 - iekļauts arī jauns uzgaļa komplekts.',
            Carbon::now()->subDays(8), $pp2b);
        $j2->update([
            JobRequest::STATUS => JobStatusEnum::ACCEPTED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a2_peteris->getId(),
            JobRequest::MASTER_ID => $m['peteris']->getId(),
            JobRequest::AGREED_PRICE => 350.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        $conv2 = $this->makeConversation($s['tonijs'], $m['peteris']);
        $this->addMessages($conv2, $s['tonijs'], $m['peteris'], [
            [$s['tonijs'], 'Sveiki, vai varat palīdzēt ar cauruļvadu remontu?', -264],
            [$m['peteris'], 'Labdien! Jā, redzēju jūsu sludinājumu. Kāds ir bojājums?', -263],
            [$s['tonijs'], 'Noplūde zem virtuves izlietnes, droši vien uzgale.', -262],
            [$m['peteris'], 'Sapratu. Nosūtīšu piedāvājumu.', -260],
            [$s['tonijs'], 'Jūsu cena ir 380 - vai 320 būtu iespējams?', -215],
            [$m['peteris'], 'Varu piedāvāt 350 ar jaunu uzgaļa komplektu iekļautu.', -190],
            [$s['tonijs'], 'Piekrītu! Pieņemts.', -189],
            [$m['peteris'], 'Lieliski! Esmu gatavs nākt pirmdien plkst. 10:00.', -188, true],
        ]);

        // 3. IN_PROGRESS - martins
        Carbon::setTestNow(Carbon::now()->subDays(14));
        $j3 = $this->makeJob($s['tonijs'], 'Dzīvokļa pilna iekštelpu krāsošana', $this->c('Iekšējā krāsošana'), 900.00, ['Rīga'],
            '2-istabu dzīvoklis 52 kv.m. Nepieciešams nokrāsot visas sienas un griestus. Vēlamā krāsa - silti balta. Materiālus var nodrošināt pats vai iekļaut cenā.');
        Carbon::setTestNow(Carbon::now()->subDays(13));
        $a3_martins = $this->makeApp($j3, $m['martins'], ApplicationStatusEnum::ACCEPTED, 800.00);
        $this->makeApp($j3, $m['roberts'], ApplicationStatusEnum::REJECTED, 950.00);
        $pp3 = $this->makeProposal($a3_martins, $m['martins'], PriceProposalStatusEnum::COUNTERED, 900.00, null,
            Carbon::now()->subDays(12));
        $pp3b = $this->makeProposal($a3_martins, $s['tonijs'], PriceProposalStatusEnum::COUNTERED, 750.00, null,
            Carbon::now()->subDays(11), $pp3);
        $pp3c = $this->makeProposal($a3_martins, $m['martins'], PriceProposalStatusEnum::ACCEPTED, 800.00,
            'Par 800 iekļauju materiālus un 2 slāņu krāsošanu.',
            Carbon::now()->subDays(10), $pp3b);
        $j3->update([
            JobRequest::STATUS => JobStatusEnum::IN_PROGRESS,
            JobRequest::ACCEPTED_APPLICATION_ID => $a3_martins->getId(),
            JobRequest::MASTER_ID => $m['martins']->getId(),
            JobRequest::AGREED_PRICE => 800.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j3->getId(),
            EscrowHold::CLIENT_ID => $s['tonijs']->getId(),
            EscrowHold::MASTER_ID => $m['martins']->getId(),
            EscrowHold::AMOUNT => 800.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(9),
        ]);
        $conv3 = $this->makeConversation($s['tonijs'], $m['martins']);
        $this->addMessages($conv3, $s['tonijs'], $m['martins'], [
            [$m['martins'], 'Sveiki! Piedāvāju 900 par pilnu dzīvokļa krāsošanu.', -312],
            [$s['tonijs'], 'Vai 750 būtu pieņemami?', -300],
            [$m['martins'], 'Varu izdarīt par 800 ar materiāliem - 2 slāņi.', -295],
            [$s['tonijs'], 'Labi, piekrītu. Kad varat sākt?', -290],
            [$m['martins'], 'Nākamā nedēļa pirmdien - vai derēs?', -289],
            [$s['tonijs'], 'Jā, lieliski. Apmaksu esmu veicis.', -220],
            [$m['martins'], 'Saņēmu apstiprinājumu. Sākam pirmdien plkst. 9.', -219],
            [$m['martins'], 'Šodien pabeidzu pirmo istabu - izskatās lieliski!', -48, true],
        ]);

        // 4. AWAITING_CONFIRMATION - ilze
        Carbon::setTestNow(Carbon::now()->subDays(25));
        $j4 = $this->makeJob($s['tonijs'], 'Pēcremonta tīrīšana dzīvoklī', $this->c('Pēcremonta tīrīšana'), 200.00, ['Rīga'],
            'Tikko beidzās remonts 3-istabu dzīvoklī. Nepieciešama profesionāla pēcremonta tīrīšana - putekļu novākšana, logu mazgāšana, grīdas.');
        Carbon::setTestNow(Carbon::now()->subDays(24));
        $a4_ilze = $this->makeApp($j4, $m['ilze'], ApplicationStatusEnum::ACCEPTED, 200.00);
        $pp4 = $this->makeProposal($a4_ilze, $m['ilze'], PriceProposalStatusEnum::ACCEPTED, 200.00,
            'Šajā cenā iekļauta pilna pēcremonta tīrīšana ar profesionālo aprīkojumu.',
            Carbon::now()->subDays(23));
        $j4->update([
            JobRequest::STATUS => JobStatusEnum::AWAITING_CONFIRMATION,
            JobRequest::ACCEPTED_APPLICATION_ID => $a4_ilze->getId(),
            JobRequest::MASTER_ID => $m['ilze']->getId(),
            JobRequest::AGREED_PRICE => 200.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(2),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j4->getId(),
            EscrowHold::CLIENT_ID => $s['tonijs']->getId(),
            EscrowHold::MASTER_ID => $m['ilze']->getId(),
            EscrowHold::AMOUNT => 200.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(20),
            EscrowHold::AUTO_RELEASE_AT => Carbon::now()->addDays(5),
        ]);
        $conv4 = $this->makeConversation($s['tonijs'], $m['ilze']);
        $this->addMessages($conv4, $s['tonijs'], $m['ilze'], [
            [$m['ilze'], 'Sveiki! Varu veikt pēcremonta tīrīšanu par 200 €.', -600],
            [$s['tonijs'], 'Lieliski, esmu apmaksājis darbu.', -500],
            [$m['ilze'], 'Paldies! Esmu pabeigusi tīrīšanu. Lūdzu apstipriniet.', -48, true],
        ]);

        // 5. COMPLETED - roberts
        Carbon::setTestNow(Carbon::now()->subDays(40));
        $j5 = $this->makeJob($s['tonijs'], 'Koka terases remonts un lakošana', $this->c('Ārējais remonts'), 500.00, ['Rīga'],
            'Koka terasei nepieciešams nomainīt 3 sapuvušas dēļus un pārlakot visu terasi ar laikapstākļiem izturīgu laku. Terase ap 20 kv.m.');
        Carbon::setTestNow(Carbon::now()->subDays(39));
        $a5_roberts = $this->makeApp($j5, $m['roberts'], ApplicationStatusEnum::ACCEPTED, 450.00);
        $this->makeApp($j5, $m['andrejs'], ApplicationStatusEnum::REJECTED, 520.00);
        $pp5 = $this->makeProposal($a5_roberts, $m['roberts'], PriceProposalStatusEnum::ACCEPTED, 450.00,
            'Iekļauj dēļu nomaiņu un divslāņu lakošanu ar Tikkurila produktiem.',
            Carbon::now()->subDays(38));
        $j5->update([
            JobRequest::STATUS => JobStatusEnum::COMPLETED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a5_roberts->getId(),
            JobRequest::MASTER_ID => $m['roberts']->getId(),
            JobRequest::AGREED_PRICE => 450.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(30),
            JobRequest::COMPLETED_AT => Carbon::now()->subDays(28),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j5->getId(),
            EscrowHold::CLIENT_ID => $s['tonijs']->getId(),
            EscrowHold::MASTER_ID => $m['roberts']->getId(),
            EscrowHold::AMOUNT => 450.00,
            EscrowHold::STATUS => EscrowStatusEnum::RELEASED,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(36),
            EscrowHold::RELEASED_AT => Carbon::now()->subDays(28),
        ]);
        Review::create([
            Review::JOB_REQUEST_ID => $j5->getId(),
            Review::REVIEWER_ID => $s['tonijs']->getId(),
            Review::REVIEWEE_ID => $m['roberts']->getId(),
            Review::RATING => 5,
            Review::COMMENT => 'Lieliski darbi! Roberts strādāja ļoti rūpīgi, terase izskatās kā jauna. Iesaku.',
        ]);

        // --- ANNA (4 darba sludinājumi) ---

        // 6. OPEN - 2 pieteikumi
        Carbon::setTestNow(Carbon::now()->subDays(2));
        $j6 = $this->makeJob($s['anna'], 'Vannas istabas flīžu nomaiņa', $this->c('Iekšējais remonts'), 600.00, ['Jūrmala'],
            'Vannas istabā (6 kv.m.) nepieciešams nomainīt vecās sienu flīzes ar jaunām. Flīzes un materiāli pieejami. Vajadzīgs tikai darbs.');
        Carbon::setTestNow(Carbon::now()->subDays(1));
        $this->makeApp($j6, $m['janis'], ApplicationStatusEnum::PENDING, 550.00);
        $this->makeApp($j6, $m['roberts'], ApplicationStatusEnum::PENDING, 620.00);
        $this->makeProposal(Application::where(Application::JOB_REQUEST_ID, $j6->getId())
            ->where(Application::USER_ID, $m['janis']->getId())->first(),
            $m['janis'], PriceProposalStatusEnum::PENDING, 550.00,
            'Varu sākt nākamajā nedēļā, cena iekļauj arī fuges un līmi.');
        $this->makeProposal(Application::where(Application::JOB_REQUEST_ID, $j6->getId())
            ->where(Application::USER_ID, $m['roberts']->getId())->first(),
            $m['roberts'], PriceProposalStatusEnum::PENDING, 620.00);

        // 7. OPEN - shortlisted, martins sarunās
        Carbon::setTestNow(Carbon::now()->subDays(5));
        $j7 = $this->makeJob($s['anna'], 'Balkona krāsošana - 2 kārtas', $this->c('Iekšējā krāsošana'), 400.00, ['Jūrmala'],
            'Balkons ap 8 kv.m. Sienas un grīda jākrāso. Vēlamos krāsas toņus izvēlēšos kopā ar meistaru. Laiks nav steidzams.');
        Carbon::setTestNow(Carbon::now()->subDays(4));
        $a7_martins = $this->makeApp($j7, $m['martins'], ApplicationStatusEnum::SHORTLISTED, 380.00);
        $pp7a = $this->makeProposal($a7_martins, $m['martins'], PriceProposalStatusEnum::COUNTERED, 420.00, null,
            Carbon::now()->subDays(3));
        $pp7b = $this->makeProposal($a7_martins, $s['anna'], PriceProposalStatusEnum::COUNTERED, 350.00,
            'Var iet arī par 350, ja darbi jāveic šomēnes.',
            Carbon::now()->subDays(2), $pp7a);
        $pp7c = $this->makeProposal($a7_martins, $m['martins'], PriceProposalStatusEnum::COUNTERED, 380.00,
            'Piedāvāju 380 - šajā cenā iekļauta gruntēšana un 2 slāņi.',
            Carbon::now()->subDays(1), $pp7b);
        $pp7d = $this->makeProposal($a7_martins, $s['anna'], PriceProposalStatusEnum::PENDING, 370.00, null,
            Carbon::now()->subHours(3), $pp7c);
        $conv7 = $this->makeConversation($s['anna'], $m['martins']);
        $this->addMessages($conv7, $s['anna'], $m['martins'], [
            [$s['anna'], 'Labdien! Vai varat apskatīt balkonu pirms tāmes?', -120],
            [$m['martins'], 'Jā, protams. Kad jums ērti?', -119],
            [$s['anna'], 'Šestvisdien ap 11:00?', -118],
            [$m['martins'], 'Derēs. Nosūtīšu piedāvājumu pēc apskatīšanas.', -96],
            [$s['anna'], 'Paldies par piedāvājumu! Vai 350 būtu iespējami?', -48],
            [$m['martins'], 'Varu izdarīt par 380 ar gruntēšanu iekļautu.', -24],
            [$s['anna'], 'Nosūtīju pretpiedāvājumu 370.', -3, true],
        ]);

        // 8. IN_PROGRESS - peteris
        Carbon::setTestNow(Carbon::now()->subDays(18));
        $j8 = $this->makeJob($s['anna'], 'Jauna dušas kabīnes uzstādīšana', $this->c('Sanitārtehnika'), 350.00, ['Jūrmala'],
            'Vecā vanna jādemontē, jāuzstāda jauna dušas kabīne 90x90. Kabīne jau iegādāta. Vajag tikai montāžu un pieslēgšanu.');
        Carbon::setTestNow(Carbon::now()->subDays(17));
        $a8_peteris = $this->makeApp($j8, $m['peteris'], ApplicationStatusEnum::ACCEPTED, 220.00);
        $this->makeApp($j8, $m['janis'], ApplicationStatusEnum::REJECTED, 280.00);
        $pp8 = $this->makeProposal($a8_peteris, $m['peteris'], PriceProposalStatusEnum::ACCEPTED, 220.00,
            'Iekļauj vecās vannas demontāžu un jaunās kabīnes pilnu uzstādīšanu.',
            Carbon::now()->subDays(16));
        $j8->update([
            JobRequest::STATUS => JobStatusEnum::IN_PROGRESS,
            JobRequest::ACCEPTED_APPLICATION_ID => $a8_peteris->getId(),
            JobRequest::MASTER_ID => $m['peteris']->getId(),
            JobRequest::AGREED_PRICE => 220.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j8->getId(),
            EscrowHold::CLIENT_ID => $s['anna']->getId(),
            EscrowHold::MASTER_ID => $m['peteris']->getId(),
            EscrowHold::AMOUNT => 220.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(14),
        ]);

        // 9. COMPLETED - ilze
        Carbon::setTestNow(Carbon::now()->subDays(45));
        $j9 = $this->makeJob($s['anna'], 'Biroja ģenerāltīrīšana', $this->c('Biroju tīrīšana'), 300.00, ['Jūrmala'],
            'Maza biroja telpas (3 istabas, ~60 kv.m.) pilna ģenerāltīrīšana. Logs, grīdas, sanitārie mezgli, virtuve. Nepieciešams līdz mēneša beigām.');
        Carbon::setTestNow(Carbon::now()->subDays(44));
        $a9_ilze = $this->makeApp($j9, $m['ilze'], ApplicationStatusEnum::ACCEPTED, 270.00);
        $pp9 = $this->makeProposal($a9_ilze, $m['ilze'], PriceProposalStatusEnum::ACCEPTED, 270.00,
            'Pilna ģenerāltīrīšana, ieskaitot logu mazgāšanu un sanitāros mezglus.',
            Carbon::now()->subDays(43));
        $j9->update([
            JobRequest::STATUS => JobStatusEnum::COMPLETED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a9_ilze->getId(),
            JobRequest::MASTER_ID => $m['ilze']->getId(),
            JobRequest::AGREED_PRICE => 270.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(35),
            JobRequest::COMPLETED_AT => Carbon::now()->subDays(33),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j9->getId(),
            EscrowHold::CLIENT_ID => $s['anna']->getId(),
            EscrowHold::MASTER_ID => $m['ilze']->getId(),
            EscrowHold::AMOUNT => 270.00,
            EscrowHold::STATUS => EscrowStatusEnum::RELEASED,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(41),
            EscrowHold::RELEASED_AT => Carbon::now()->subDays(33),
        ]);
        Review::create([
            Review::JOB_REQUEST_ID => $j9->getId(),
            Review::REVIEWER_ID => $s['anna']->getId(),
            Review::REVIEWEE_ID => $m['ilze']->getId(),
            Review::RATING => 4,
            Review::COMMENT => 'Ilze strādāja ātri un rūpīgi. Birojs izskatās lieliski. Nelielas komentāri par loga stūriem, bet kopumā ļoti apmierināta.',
        ]);

        // --- KĀRLIS (4 darba sludinājumi) ---

        // 10. OPEN - 0 pieteikumi
        Carbon::setTestNow(Carbon::now()->subDays(1));
        $this->makeJob($s['karlis'], 'Elektroinstalācija garāžā', $this->c('Iekšējie darbi'), 400.00, ['Liepāja'],
            'Garāžā nepieciešams izviest elektroinstalāciju - 3 rozetes, 2 lampu punkti un drošinātāju kaste. Garāža 24 kv.m., no nulles.');

        // 11. IN_PROGRESS - martins
        Carbon::setTestNow(Carbon::now()->subDays(10));
        $j11 = $this->makeJob($s['karlis'], 'Dzīvokļa sienu krāsošana', $this->c('Iekšējā krāsošana'), 600.00, ['Liepāja'],
            '3-istabu dzīvoklis, sienas un griesti jākrāso. Materiāli pieejami. Vēlos skaistu, tīru darbu bez strīpām un pilēšanas.');
        Carbon::setTestNow(Carbon::now()->subDays(9));
        $a11_martins = $this->makeApp($j11, $m['martins'], ApplicationStatusEnum::ACCEPTED, 540.00);
        $this->makeApp($j11, $m['roberts'], ApplicationStatusEnum::REJECTED, 650.00);
        $pp11 = $this->makeProposal($a11_martins, $m['martins'], PriceProposalStatusEnum::ACCEPTED, 540.00,
            'Par 540 iekļauts gruntojums un 2 krāsas slāņi ar Tikkurila.',
            Carbon::now()->subDays(8));
        $j11->update([
            JobRequest::STATUS => JobStatusEnum::IN_PROGRESS,
            JobRequest::ACCEPTED_APPLICATION_ID => $a11_martins->getId(),
            JobRequest::MASTER_ID => $m['martins']->getId(),
            JobRequest::AGREED_PRICE => 540.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j11->getId(),
            EscrowHold::CLIENT_ID => $s['karlis']->getId(),
            EscrowHold::MASTER_ID => $m['martins']->getId(),
            EscrowHold::AMOUNT => 540.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(7),
        ]);

        // 12. COMPLETED - peteris
        Carbon::setTestNow(Carbon::now()->subDays(50));
        $j12 = $this->makeJob($s['karlis'], 'Vannas istabas santehnika no nulles', $this->c('Sanitārtehnika'), 800.00, ['Liepāja'],
            'Jaunbūvē nepieciešams izvest visu santehnikas sistēmu vannas istabā - cauruļvadi, pieslēgumi, uzstādīt sanitārtehnikas elementus.');
        Carbon::setTestNow(Carbon::now()->subDays(49));
        $a12_peteris = $this->makeApp($j12, $m['peteris'], ApplicationStatusEnum::ACCEPTED, 750.00);
        $pp12 = $this->makeProposal($a12_peteris, $m['peteris'], PriceProposalStatusEnum::ACCEPTED, 750.00, null,
            Carbon::now()->subDays(48));
        $j12->update([
            JobRequest::STATUS => JobStatusEnum::COMPLETED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a12_peteris->getId(),
            JobRequest::MASTER_ID => $m['peteris']->getId(),
            JobRequest::AGREED_PRICE => 750.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(40),
            JobRequest::COMPLETED_AT => Carbon::now()->subDays(38),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j12->getId(),
            EscrowHold::CLIENT_ID => $s['karlis']->getId(),
            EscrowHold::MASTER_ID => $m['peteris']->getId(),
            EscrowHold::AMOUNT => 750.00,
            EscrowHold::STATUS => EscrowStatusEnum::RELEASED,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(46),
            EscrowHold::RELEASED_AT => Carbon::now()->subDays(38),
        ]);
        Review::create([
            Review::JOB_REQUEST_ID => $j12->getId(),
            Review::REVIEWER_ID => $s['karlis']->getId(),
            Review::REVIEWEE_ID => $m['peteris']->getId(),
            Review::RATING => 3,
            Review::COMMENT => 'Darbs izpildīts, taču ar nelielu kavēšanos. Kvalitāte laba, bet komunikācija varētu būt labāka.',
        ]);

        // 13. DISPUTED - linda
        Carbon::setTestNow(Carbon::now()->subDays(30));
        $j13 = $this->makeJob($s['karlis'], 'Dārza labiekārtošana ar stādījumiem', $this->c('Stādīšana'), 1200.00, ['Liepāja'],
            'Liela dārza labiekārtošana - dzīvžogs, stādījumi, taciņu ieklāšana. Projekts pieejams. Darbiem jāsākas maijā.');
        Carbon::setTestNow(Carbon::now()->subDays(29));
        $a13_linda = $this->makeApp($j13, $m['linda'], ApplicationStatusEnum::ACCEPTED, 1100.00);
        $pp13 = $this->makeProposal($a13_linda, $m['linda'], PriceProposalStatusEnum::ACCEPTED, 1100.00, null,
            Carbon::now()->subDays(28));
        $j13->update([
            JobRequest::STATUS => JobStatusEnum::DISPUTED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a13_linda->getId(),
            JobRequest::MASTER_ID => $m['linda']->getId(),
            JobRequest::AGREED_PRICE => 1100.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j13->getId(),
            EscrowHold::CLIENT_ID => $s['karlis']->getId(),
            EscrowHold::MASTER_ID => $m['linda']->getId(),
            EscrowHold::AMOUNT => 1100.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(25),
        ]);
        JobDispute::create([
            JobDispute::JOB_REQUEST_ID => $j13->getId(),
            JobDispute::RAISED_BY_USER_ID => $s['karlis']->getId(),
            JobDispute::REASON => 'Meistare ir paveikusi tikai daļu no vienotajiem darbiem - dzīvžogs nav ierīkots un stādījumi ir nepareizas šķirnes. Lūdzu atgriezt daļu maksājuma.',
        ]);

        // --- MADARA (4 darba sludinājumi) ---

        // 14. OPEN - 1 pieteikums (kristine)
        Carbon::setTestNow(Carbon::now()->subDays(3));
        $j14 = $this->makeJob($s['madara'], 'Logu mazgāšana privātmājā', $this->c('Stiklu pakalpojumi'), 120.00, ['Valmiera'],
            'Privātmājā 14 logi jānomazgā iekšpusē un ārpusē. Māja 2 stāvi. Vēlams paveikt šajā nedēļā.');
        Carbon::setTestNow(Carbon::now()->subDays(2));
        $a14_kristine = $this->makeApp($j14, $m['kristine'], ApplicationStatusEnum::PENDING, 110.00);
        $this->makeProposal($a14_kristine, $m['kristine'], PriceProposalStatusEnum::PENDING, 110.00,
            'Par 110 veikšu pilnu logu mazgāšanu iekšpusē un ārpusē, iekļaujot palodzes.');

        // 15. ACCEPTED - ilze
        Carbon::setTestNow(Carbon::now()->subDays(8));
        $j15 = $this->makeJob($s['madara'], 'Dzīvokļa uzkopšana īres beigās', $this->c('Dzīvokļu uzkopšana'), 150.00, ['Valmiera'],
            '2-istabu dzīvoklis, īres periods beidzas. Nepieciešama pilna uzkopšana, lai atgūtu depozītu.');
        Carbon::setTestNow(Carbon::now()->subDays(7));
        $a15_ilze = $this->makeApp($j15, $m['ilze'], ApplicationStatusEnum::ACCEPTED, 130.00);
        $pp15 = $this->makeProposal($a15_ilze, $m['ilze'], PriceProposalStatusEnum::ACCEPTED, 130.00,
            'Iekļauj pilnu uzkopšanu ar logu un sadzīves tehnikas tīrīšanu.',
            Carbon::now()->subDays(6));
        $j15->update([
            JobRequest::STATUS => JobStatusEnum::ACCEPTED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a15_ilze->getId(),
            JobRequest::MASTER_ID => $m['ilze']->getId(),
            JobRequest::AGREED_PRICE => 130.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);

        // 16. IN_PROGRESS - roberts
        Carbon::setTestNow(Carbon::now()->subDays(20));
        $j16 = $this->makeJob($s['madara'], 'Guļamistabas pilns iekštelpu remonts', $this->c('Iekšējais remonts'), 2000.00, ['Valmiera'],
            'Guļamistabā nepieciešams nomainīt grīdu (lamināts), nokrāsot sienas, uzstādīt jaunas iekšdurvis. Platība 18 kv.m.');
        Carbon::setTestNow(Carbon::now()->subDays(19));
        $a16_roberts = $this->makeApp($j16, $m['roberts'], ApplicationStatusEnum::ACCEPTED, 1800.00);
        $pp16 = $this->makeProposal($a16_roberts, $m['roberts'], PriceProposalStatusEnum::COUNTERED, 2100.00, null,
            Carbon::now()->subDays(18));
        $pp16b = $this->makeProposal($a16_roberts, $s['madara'], PriceProposalStatusEnum::COUNTERED, 1700.00, null,
            Carbon::now()->subDays(17), $pp16);
        $pp16c = $this->makeProposal($a16_roberts, $m['roberts'], PriceProposalStatusEnum::ACCEPTED, 1800.00,
            'Par 1800 iekļauju laminātu, durvju montāžu un sienu krāsošanu.',
            Carbon::now()->subDays(16), $pp16b);
        $j16->update([
            JobRequest::STATUS => JobStatusEnum::IN_PROGRESS,
            JobRequest::ACCEPTED_APPLICATION_ID => $a16_roberts->getId(),
            JobRequest::MASTER_ID => $m['roberts']->getId(),
            JobRequest::AGREED_PRICE => 1800.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j16->getId(),
            EscrowHold::CLIENT_ID => $s['madara']->getId(),
            EscrowHold::MASTER_ID => $m['roberts']->getId(),
            EscrowHold::AMOUNT => 1800.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(14),
        ]);

        // 17. COMPLETED - peteris
        Carbon::setTestNow(Carbon::now()->subDays(55));
        $j17 = $this->makeJob($s['madara'], 'Ūdens sildītāja uzstādīšana', $this->c('Ūdens sildītāji'), 200.00, ['Valmiera'],
            'Vecais ūdens sildītājs jānomaina ar jaunu 80L tilpuma ierīci. Sildītājs iegādāts - nepieciešama demontāža un uzstādīšana.');
        Carbon::setTestNow(Carbon::now()->subDays(54));
        $a17_peteris = $this->makeApp($j17, $m['peteris'], ApplicationStatusEnum::ACCEPTED, 120.00);
        $pp17 = $this->makeProposal($a17_peteris, $m['peteris'], PriceProposalStatusEnum::ACCEPTED, 120.00, null,
            Carbon::now()->subDays(53));
        $j17->update([
            JobRequest::STATUS => JobStatusEnum::COMPLETED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a17_peteris->getId(),
            JobRequest::MASTER_ID => $m['peteris']->getId(),
            JobRequest::AGREED_PRICE => 120.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(46),
            JobRequest::COMPLETED_AT => Carbon::now()->subDays(44),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j17->getId(),
            EscrowHold::CLIENT_ID => $s['madara']->getId(),
            EscrowHold::MASTER_ID => $m['peteris']->getId(),
            EscrowHold::AMOUNT => 120.00,
            EscrowHold::STATUS => EscrowStatusEnum::RELEASED,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(50),
            EscrowHold::RELEASED_AT => Carbon::now()->subDays(44),
        ]);
        Review::create([
            Review::JOB_REQUEST_ID => $j17->getId(),
            Review::REVIEWER_ID => $s['madara']->getId(),
            Review::REVIEWEE_ID => $m['peteris']->getId(),
            Review::RATING => 5,
            Review::COMMENT => 'Pēteris ieradās precīzi laikā un uzstādīja sildītāju ļoti ātri. Viss darbojas perfekti. Iesaku!',
        ]);

        // --- GUNĀRS (5 darba sludinājumi) ---

        // 18. OPEN - roberts shortlisted, sarunas; andrejs, janis gaida
        Carbon::setTestNow(Carbon::now()->subDays(6));
        $j18 = $this->makeJob($s['gunars'], 'Koka žogs apkārt dārzam', $this->c('Ārējais remonts'), 1500.00, ['Daugavpils'],
            'Nepieciešams uzbūvēt koka žogu ap 60 metru garumā. Žogam jābūt izturīgam, ar vārtiem. Impregnēts koks vai teraskoks - pēc meistara ieteikuma.');
        Carbon::setTestNow(Carbon::now()->subDays(5));
        $a18_roberts = $this->makeApp($j18, $m['roberts'], ApplicationStatusEnum::SHORTLISTED, 1350.00);
        $a18_andrejs = $this->makeApp($j18, $m['andrejs'], ApplicationStatusEnum::PENDING, 1600.00);
        $a18_janis = $this->makeApp($j18, $m['janis'], ApplicationStatusEnum::PENDING, 1400.00);
        $pp18a = $this->makeProposal($a18_roberts, $m['roberts'], PriceProposalStatusEnum::COUNTERED, 1500.00,
            'Iekļauj teraskoku un vārtus ar atslēgu.',
            Carbon::now()->subDays(4));
        $pp18b = $this->makeProposal($a18_roberts, $s['gunars'], PriceProposalStatusEnum::COUNTERED, 1250.00, null,
            Carbon::now()->subDays(3), $pp18a);
        $pp18c = $this->makeProposal($a18_roberts, $m['roberts'], PriceProposalStatusEnum::COUNTERED, 1350.00,
            'Labākā cena, ko varu piedāvāt ar teraskoku.',
            Carbon::now()->subDays(2), $pp18b);
        $pp18d = $this->makeProposal($a18_roberts, $s['gunars'], PriceProposalStatusEnum::PENDING, 1300.00, null,
            Carbon::now()->subHours(6), $pp18c);
        $this->makeProposal($a18_andrejs, $m['andrejs'], PriceProposalStatusEnum::PENDING, 1600.00);
        $this->makeProposal($a18_janis,   $m['janis'],   PriceProposalStatusEnum::PENDING, 1400.00,
            'Varu sākt šajā nedēļas nogalē.');
        $conv18 = $this->makeConversation($s['gunars'], $m['roberts']);
        $this->addMessages($conv18, $s['gunars'], $m['roberts'], [
            [$s['gunars'], 'Sveiki Roberts, redzu piedāvājāt žogu - izskatās labi.', -144],
            [$m['roberts'], 'Jā, šāda veida darbi man ir ikdiena. Ko vēlaties - koku vai metālu?', -143],
            [$s['gunars'], 'Koku - impregnētu. 60 metri ar vārtiem.', -142],
            [$m['roberts'], 'Sapratu, esmu nosūtījis detalizētu piedāvājumu.', -120],
            [$s['gunars'], 'Cena ir augstāka par budžetu. Vai varam vienoties?', -72],
            [$m['roberts'], 'Nosūtīšu atjauninātu piedāvājumu ar teraskoku.', -48],
            [$s['gunars'], 'Labi, esmu nosūtījis pretpiedāvājumu 1300.', -6, true],
        ]);

        // 19. OPEN - 1 pieteikums (andrejs)
        Carbon::setTestNow(Carbon::now()->subDays(3));
        $j19 = $this->makeJob($s['gunars'], 'Ārējā apgaismojuma uzstādīšana mājai', $this->c('Ārējie darbi'), 350.00, ['Daugavpils'],
            'Nepieciešams uzstādīt 6 ārējās sienas lampu un 1 automātisko kustības sensoru. Kabeļi jāpavada mājas ārpusē.');
        Carbon::setTestNow(Carbon::now()->subDays(2));
        $a19_andrejs = $this->makeApp($j19, $m['andrejs'], ApplicationStatusEnum::PENDING, 320.00);
        $this->makeProposal($a19_andrejs, $m['andrejs'], PriceProposalStatusEnum::PENDING, 320.00,
            'Iekļauj 6 lampu montāžu, sensoru un kabeļu vadu. Varu sākt pēc nedēļas.');

        // 20. AWAITING_CONFIRMATION - kristine
        Carbon::setTestNow(Carbon::now()->subDays(22));
        $j20 = $this->makeJob($s['gunars'], 'Logu mazgāšana daudzdzīvokļu mājā', $this->c('Stiklu pakalpojumi'), 200.00, ['Daugavpils'],
            '5-stāvu daudzdzīvokļu mājas kāpņu telpas logi jānomazgā - 20 logi. Ārpusē no kāpnēm ar teleskopisko slotu.');
        Carbon::setTestNow(Carbon::now()->subDays(21));
        $a20_kristine = $this->makeApp($j20, $m['kristine'], ApplicationStatusEnum::ACCEPTED, 160.00);
        $pp20 = $this->makeProposal($a20_kristine, $m['kristine'], PriceProposalStatusEnum::ACCEPTED, 160.00, null,
            Carbon::now()->subDays(20));
        $j20->update([
            JobRequest::STATUS => JobStatusEnum::AWAITING_CONFIRMATION,
            JobRequest::ACCEPTED_APPLICATION_ID => $a20_kristine->getId(),
            JobRequest::MASTER_ID => $m['kristine']->getId(),
            JobRequest::AGREED_PRICE => 160.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(3),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j20->getId(),
            EscrowHold::CLIENT_ID => $s['gunars']->getId(),
            EscrowHold::MASTER_ID => $m['kristine']->getId(),
            EscrowHold::AMOUNT => 160.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(18),
            EscrowHold::AUTO_RELEASE_AT => Carbon::now()->addDays(5),
        ]);
        $conv20 = $this->makeConversation($s['gunars'], $m['kristine']);
        $this->addMessages($conv20, $s['gunars'], $m['kristine'], [
            [$m['kristine'], 'Sveiki! Varu veikt logu mazgāšanu par 160.', -528],
            [$s['gunars'], 'Lieliski, pieņemts. Esmu apmaksājis.', -480],
            [$m['kristine'], 'Paldies! Darbus esmu pabeigusi, lūdzu apstipriniet.', -72, true],
        ]);

        // 21. COMPLETED - linda
        Carbon::setTestNow(Carbon::now()->subDays(60));
        $j21 = $this->makeJob($s['gunars'], 'Dārza ikgadējā sakopšana pavasarī', $this->c('Zāles pļaušana'), 400.00, ['Daugavpils'],
            'Liels dārzs (~500 kv.m.) pēc ziemas - zāles pirmā pļaušana, krūmu apgriešana, lapu savākšana, taciņu sakārtošana.');
        Carbon::setTestNow(Carbon::now()->subDays(59));
        $a21_linda = $this->makeApp($j21, $m['linda'], ApplicationStatusEnum::ACCEPTED, 380.00);
        $pp21 = $this->makeProposal($a21_linda, $m['linda'], PriceProposalStatusEnum::ACCEPTED, 380.00, null,
            Carbon::now()->subDays(58));
        $j21->update([
            JobRequest::STATUS => JobStatusEnum::COMPLETED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a21_linda->getId(),
            JobRequest::MASTER_ID => $m['linda']->getId(),
            JobRequest::AGREED_PRICE => 380.00,
            JobRequest::PRICE_TYPE => 'fixed',
            JobRequest::MASTER_COMPLETED_AT => Carbon::now()->subDays(50),
            JobRequest::COMPLETED_AT => Carbon::now()->subDays(48),
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j21->getId(),
            EscrowHold::CLIENT_ID => $s['gunars']->getId(),
            EscrowHold::MASTER_ID => $m['linda']->getId(),
            EscrowHold::AMOUNT => 380.00,
            EscrowHold::STATUS => EscrowStatusEnum::RELEASED,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(56),
            EscrowHold::RELEASED_AT => Carbon::now()->subDays(48),
        ]);
        Review::create([
            Review::JOB_REQUEST_ID => $j21->getId(),
            Review::REVIEWER_ID => $s['gunars']->getId(),
            Review::REVIEWEE_ID => $m['linda']->getId(),
            Review::RATING => 5,
            Review::COMMENT => 'Linda ir lieliska! Dārzs izskatās brīnišķīgi pēc viņas darba. Noteikti uzaicināšu atkal.',
        ]);

        // 22. CANCELLED - gunars
        Carbon::setTestNow(Carbon::now()->subDays(15));
        $j22 = $this->makeJob($s['gunars'], 'Mājas sienu iekštelpu remonts', $this->c('Iekšējais remonts'), 3000.00, ['Daugavpils'],
            'Liels iekštelpu renovācijas projekts - plānoju atcelt, jo maināmies uz citu māju. Pagaidām meklēju tāmes.');
        Carbon::setTestNow(Carbon::now()->subDays(14));
        $a22_roberts = $this->makeApp($j22, $m['roberts'], ApplicationStatusEnum::PENDING, 2800.00);
        $this->makeProposal($a22_roberts, $m['roberts'], PriceProposalStatusEnum::PENDING, 2800.00,
            'Detalizēta tāme - varu nosūtīt pēc apskates.');
        $j22->update([JobRequest::STATUS => JobStatusEnum::CANCELLED]);

        // --- JĀNIS KĀ DARBA MEKLĒTĀJS (3 darba sludinājumi) ---

        // 23. OPEN - roberts gaida
        Carbon::setTestNow(Carbon::now()->subDays(4));
        $j23 = $this->makeJob($s['janis'], 'Flīžu labošana vannas istabā', $this->c('Iekšējais remonts'), 150.00, ['Rīga'],
            'Vannas istabā 3 flīzes ir ieplaisājušas un viena ir atkritusi. Vajadzīga nomaiņa un fugas atjaunošana ap tām.');
        Carbon::setTestNow(Carbon::now()->subDays(3));
        $a23_roberts = $this->makeApp($j23, $m['roberts'], ApplicationStatusEnum::PENDING, 140.00);
        $this->makeProposal($a23_roberts, $m['roberts'], PriceProposalStatusEnum::PENDING, 140.00,
            'Varu salabota 3 flīzes un atjaunot fugas. Materiāli iekļauti.');

        // 24. ACCEPTED - andrejs
        Carbon::setTestNow(Carbon::now()->subDays(9));
        $j24 = $this->makeJob($s['janis'], 'Elektroapgaismojums mājas dārzā', $this->c('Ārējie darbi'), 320.00, ['Rīga'],
            'Dārzā nepieciešams uzstādīt 4 zemē ierāktus LED gaismekļus un 2 sienas lampas. Kabeļu vads jāieklāj zemē.');
        Carbon::setTestNow(Carbon::now()->subDays(8));
        $a24_andrejs = $this->makeApp($j24, $m['andrejs'], ApplicationStatusEnum::ACCEPTED, 280.00);
        $pp24 = $this->makeProposal($a24_andrejs, $m['andrejs'], PriceProposalStatusEnum::ACCEPTED, 280.00,
            'Iekļauj kabeļu vadu zemē, 6 gaismekļu montāžu un pieslēgšanu.',
            Carbon::now()->subDays(7));
        $j24->update([
            JobRequest::STATUS => JobStatusEnum::ACCEPTED,
            JobRequest::ACCEPTED_APPLICATION_ID => $a24_andrejs->getId(),
            JobRequest::MASTER_ID => $m['andrejs']->getId(),
            JobRequest::AGREED_PRICE => 280.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);

        // 25. IN_PROGRESS - linda
        Carbon::setTestNow(Carbon::now()->subDays(16));
        $j25 = $this->makeJob($s['janis'], 'Koka dekoratīvais žogs terasei', $this->c('Ārējais remonts'), 700.00, ['Rīga'],
            'Terases malā nepieciešams koka dekoratīvais žogs ap 15 metru garumā. Augstums ~1m. Jāizstrādā dizains kopā ar meistaru.');
        Carbon::setTestNow(Carbon::now()->subDays(15));
        $a25_linda = $this->makeApp($j25, $m['linda'], ApplicationStatusEnum::ACCEPTED, 650.00);
        $pp25 = $this->makeProposal($a25_linda, $m['linda'], PriceProposalStatusEnum::ACCEPTED, 650.00,
            'Iekļauj dizainu, materiālus un montāžu. Teraskoks ar tonēšanu.',
            Carbon::now()->subDays(14));
        $j25->update([
            JobRequest::STATUS => JobStatusEnum::IN_PROGRESS,
            JobRequest::ACCEPTED_APPLICATION_ID => $a25_linda->getId(),
            JobRequest::MASTER_ID => $m['linda']->getId(),
            JobRequest::AGREED_PRICE => 650.00,
            JobRequest::PRICE_TYPE => 'fixed',
        ]);
        EscrowHold::create([
            EscrowHold::JOB_REQUEST_ID => $j25->getId(),
            EscrowHold::CLIENT_ID => $s['janis']->getId(),
            EscrowHold::MASTER_ID => $m['linda']->getId(),
            EscrowHold::AMOUNT => 650.00,
            EscrowHold::STATUS => EscrowStatusEnum::HELD,
            EscrowHold::PAYMENT_REFERENCE => 'mock_' . Str::uuid(),
            EscrowHold::HELD_AT => Carbon::now()->subDays(12),
        ]);

        Carbon::setTestNow(null);
    }

    // --- Palīgfunkcijas ---

    private function makeJob(User $seeker, string $title, Category $cat, float $budget,
        array $location, string $description): JobRequest
    {
        return JobRequest::create([
            JobRequest::USER_ID => $seeker->getId(),
            JobRequest::CATEGORY_ID => $cat->getId(),
            JobRequest::TITLE => $title,
            JobRequest::SLUG => Str::slug($title) . '-' . Str::random(5),
            JobRequest::DESCRIPTION => $description,
            JobRequest::BUDGET => $budget,
            JobRequest::LOCATION => $location,
            JobRequest::DEADLINE => Carbon::now()->addDays(30)->toDateString(),
            JobRequest::STATUS => JobStatusEnum::OPEN,
            JobRequest::IMAGES => [],
        ]);
    }

    private function makeApp(JobRequest $job, User $master, ApplicationStatusEnum $status,
        float $priceOffer): Application
    {
        return Application::create([
            Application::JOB_REQUEST_ID => $job->getId(),
            Application::USER_ID => $master->getId(),
            Application::COVER_LETTER => 'Esmu ieinteresēts šajā darbā un varu to veikt kvalitatīvi un savlaicīgi.',
            Application::PRICE_OFFER => $priceOffer,
            Application::STATUS => $status,
        ]);
    }

    private function makeProposal(Application $app, User $proposedBy,
        PriceProposalStatusEnum $status, float $amount, ?string $note = null,
        ?Carbon $createdAt = null, ?PriceProposal $responding = null): PriceProposal
    {
        if ($createdAt) {
            Carbon::setTestNow($createdAt);
        }

        $data = [
            PriceProposal::JOB_APPLICATION_ID => $app->getId(),
            PriceProposal::PROPOSED_BY_USER_ID => $proposedBy->getId(),
            PriceProposal::AMOUNT => $amount,
            PriceProposal::PRICE_TYPE => 'fixed',
            PriceProposal::NOTE => $note,
            PriceProposal::STATUS => $status,
        ];

        if ($responding && $status !== PriceProposalStatusEnum::PENDING) {
            $data[PriceProposal::RESPONDED_BY_USER_ID] = $proposedBy->getId();
            $data[PriceProposal::RESPONDED_AT] = now();
        }

        return PriceProposal::create($data);
    }

    private function makeConversation(User $u1, User $u2): Conversation
    {
        return Conversation::firstOrCreate(
            [Conversation::SENDER_ID => $u1->getId(), Conversation::RECEIVER_ID => $u2->getId()],
            [Conversation::SENDER_ID => $u1->getId(), Conversation::RECEIVER_ID => $u2->getId()]
        );
    }

    /**
     * @param array<array{0: User, 1: string, 2: int, 3?: bool}> $messages
     *   Each entry: [sender, body, hoursAgo, unread?]
     */
    private function addMessages(Conversation $conv, User $u1, User $u2, array $messages): void
    {
        $base = Carbon::now();
        foreach ($messages as $msg) {
            [$sender, $body, $hoursAgo] = $msg;
            $unread = $msg[3] ?? false;
            $sentAt = $base->copy()->addHours($hoursAgo);
            Carbon::setTestNow($sentAt);
            Message::create([
                Message::CONVERSATION_ID => $conv->getId(),
                Message::SENDER_ID => $sender->getId(),
                Message::BODY => $body,
                Message::TYPE => MessageTypeEnum::TEXT,
                Message::READ_AT => $unread ? null : $sentAt->copy()->addMinutes(5),
            ]);
        }
        Carbon::setTestNow(null);
    }

    // --- Kategoriju ieteikumi ---

    private function createCategorySuggestions(): void
    {
        $citsId = $this->cats['Cits']->getId();
        $apkureId = $this->cats['Apkure un siltumtehnika']->getId();
        $darzaId = $this->cats['Dārza darbi']->getId();

        // 3 gaida apstiprināšanu
        Carbon::setTestNow(Carbon::now()->subDays(3));
        $s1 = CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->seekers['tonijs']->getId(),
            CategorySuggestion::NAME => 'Sniega tīrīšana no jumtiem',
            CategorySuggestion::PARENT_CATEGORY_ID => null,
            CategorySuggestion::NOTE => 'Bieži meklēju šo pakalpojumu ziemā, bet nav atbilstošas kategorijas.',
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::PENDING,
        ]);
        // Pievieno darbu, lai parādītu gaidīšanas joslu
        $job = JobRequest::where(JobRequest::USER_ID, $this->seekers['tonijs']->getId())
            ->where(JobRequest::STATUS, JobStatusEnum::OPEN)->first();
        if ($job) {
            $job->update([
                JobRequest::CATEGORY_ID => $citsId,
                JobRequest::PENDING_CATEGORY_SUGGESTION_ID => $s1->getId(),
            ]);
        }

        Carbon::setTestNow(Carbon::now()->subDays(2));
        CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->masters['andrejs']->getId(),
            CategorySuggestion::NAME => 'Termiskā kamīna uzstādīšana',
            CategorySuggestion::PARENT_CATEGORY_ID => $apkureId,
            CategorySuggestion::NOTE => 'Šis ir bieži pieprasīts pakalpojums, kas neatbilst esošajām apakškategorijām.',
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::PENDING,
        ]);

        Carbon::setTestNow(Carbon::now()->subDays(1));
        CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->seekers['anna']->getId(),
            CategorySuggestion::NAME => 'Dārza laistīšanas sistēmas',
            CategorySuggestion::PARENT_CATEGORY_ID => $darzaId,
            CategorySuggestion::NOTE => 'Meklēju laistīšanas sistēmu uzstādīšanu, bet nav piemērotas kategorijas.',
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::PENDING,
        ]);

        // 2 apstiprināti ieteikumi
        Carbon::setTestNow(Carbon::now()->subDays(20));
        CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->seekers['karlis']->getId(),
            CategorySuggestion::NAME => 'Siltumsūkņu uzstādīšana',
            CategorySuggestion::PARENT_CATEGORY_ID => $apkureId,
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::APPROVED,
            CategorySuggestion::REVIEWED_BY_USER_ID => $this->admin->getId(),
            CategorySuggestion::REVIEWED_AT => Carbon::now()->subDays(15),
            CategorySuggestion::REVIEW_NOTE => 'Laba ideja - kategorija jau eksistē kā "Siltumsūkņi".',
        ]);

        Carbon::setTestNow(Carbon::now()->subDays(30));
        CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->seekers['madara']->getId(),
            CategorySuggestion::NAME => 'Āra baseinu tīrīšana',
            CategorySuggestion::PARENT_CATEGORY_ID => null,
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::APPROVED,
            CategorySuggestion::REVIEWED_BY_USER_ID => $this->admin->getId(),
            CategorySuggestion::REVIEWED_AT => Carbon::now()->subDays(22),
            CategorySuggestion::REVIEW_NOTE => 'Izveidota kā jauna apakškategorija zem Tīrīšanas.',
        ]);

        // 1 noraidīts ieteikums
        Carbon::setTestNow(Carbon::now()->subDays(25));
        CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->seekers['gunars']->getId(),
            CategorySuggestion::NAME => 'Auto mazgāšana',
            CategorySuggestion::PARENT_CATEGORY_ID => null,
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::REJECTED,
            CategorySuggestion::REVIEWED_BY_USER_ID => $this->admin->getId(),
            CategorySuggestion::REVIEWED_AT => Carbon::now()->subDays(20),
            CategorySuggestion::REVIEW_NOTE => 'Platforma ir paredzēta mājas pakalpojumiem, auto mazgāšana nav mūsu fokusā.',
        ]);

        // 1 sapludināts ieteikums
        Carbon::setTestNow(Carbon::now()->subDays(35));
        CategorySuggestion::create([
            CategorySuggestion::SUGGESTED_BY_USER_ID => $this->masters['linda']->getId(),
            CategorySuggestion::NAME => 'Dārza lapiņu tīrīšana',
            CategorySuggestion::PARENT_CATEGORY_ID => $darzaId,
            CategorySuggestion::STATUS => CategorySuggestionStatusEnum::MERGED,
            CategorySuggestion::REVIEWED_BY_USER_ID => $this->admin->getId(),
            CategorySuggestion::REVIEWED_AT => Carbon::now()->subDays(28),
            CategorySuggestion::REVIEW_NOTE => 'Apvienota ar esošo "Zāles pļaušana" kategoriju, jo tās ir saistītas.',
            CategorySuggestion::RESULTING_CATEGORY_ID => $this->cats['Zāles pļaušana']->getId(),
        ]);

        Carbon::setTestNow(null);
    }

    // --- Paziņojumi ---

    private function createNotifications(): void
    {
        $now = Carbon::now();

        // Palīgfunkcija: atrod darbu pēc nosaukuma un atgriež tā URL
        $jobUrl = function (string $title, string $fallback = '/dashboard'): string {
            $j = JobRequest::where(JobRequest::TITLE, $title)->first();
            return $j ? '/jobs/' . $j->getId() : $fallback;
        };

        // Administrators - jauni kategoriju ieteikumi
        $this->notify($this->admin, NotificationTypeEnum::NEW_CATEGORY_SUGGESTION,
            'Jauns kategorijas ieteikums', 'Tonijs Bāliņš ieteicis jaunu kategoriju: "Sniega tīrīšana no jumtiem".',
            '/admin/category-suggestions', $now->copy()->subDays(3));
        $this->notify($this->admin, NotificationTypeEnum::NEW_CATEGORY_SUGGESTION,
            'Jauns kategorijas ieteikums', 'Andrejs Bērziņš ieteicis apakškategoriju: "Termiskā kamīna uzstādīšana".',
            '/admin/category-suggestions', $now->copy()->subDays(2));
        $this->notify($this->admin, NotificationTypeEnum::NEW_CATEGORY_SUGGESTION,
            'Jauns kategorijas ieteikums', 'Anna Riekstiņa ieteikusi apakškategoriju: "Dārza laistīšanas sistēmas".',
            '/admin/category-suggestions', null);

        // Andrejs (meistars) - dažādi notikumi
        $this->notify($this->masters['andrejs'], NotificationTypeEnum::APPLICATION_SHORTLISTED,
            'Jūsu pieteikums apsvērts', 'Tonijs Bāliņš apsvēra jūsu pieteikumu darbam "Elektroinstalācija viesistabā".',
            $jobUrl('Elektroinstalācija viesistabā', '/master/my-applications'), $now->copy()->subDays(3));
        $this->notify($this->masters['andrejs'], NotificationTypeEnum::PROPOSAL_RECEIVED,
            'Jauns pretpiedāvājums', 'Tonijs Bāliņš nosūtīja pretpiedāvājumu 240 EUR.',
            $jobUrl('Elektroinstalācija viesistabā', '/master/my-applications'), $now->copy()->subDays(2));
        $this->notify($this->masters['andrejs'], NotificationTypeEnum::APPLICATION_ACCEPTED,
            'Pieteikums pieņemts!', 'Jānis Auziņš pieņēmis jūsu pieteikumu darbam "Elektroapgaismojums mājas dārzā".',
            $jobUrl('Elektroapgaismojums mājas dārzā', '/master/my-applications'), $now->copy()->subDays(7));
        $this->notify($this->masters['andrejs'], NotificationTypeEnum::JOB_PAID,
            'Darba apmaksa saņemta', 'Jānis Auziņš ir apmaksājis darbu "Elektroapgaismojums mājas dārzā".',
            $jobUrl('Elektroapgaismojums mājas dārzā', '/master/my-applications'), null);

        // Pēteris (meistars)
        $this->notify($this->masters['peteris'], NotificationTypeEnum::APPLICATION_ACCEPTED,
            'Pieteikums pieņemts!', 'Tonijs Bāliņš pieņēmis jūsu pieteikumu darbam "Kanalizācijas caurules noplūde".',
            $jobUrl('Kanalizācijas caurules noplūde virtuvē', '/master/my-applications'), $now->copy()->subDays(8));
        $this->notify($this->masters['peteris'], NotificationTypeEnum::PROPOSAL_ACCEPTED,
            'Piedāvājums pieņemts', 'Jūsu cenu piedāvājums 350 EUR ir pieņemts.',
            $jobUrl('Kanalizācijas caurules noplūde virtuvē', '/master/my-applications'), $now->copy()->subDays(8));

        // Mārtiņš (meistars)
        $this->notify($this->masters['martins'], NotificationTypeEnum::APPLICATION_SHORTLISTED,
            'Jūsu pieteikums apsvērts', 'Anna Riekstiņa apsvēra jūsu pieteikumu "Balkona krāsošana".',
            $jobUrl('Balkona krāsošana - 2 kārtas', '/master/my-applications'), $now->copy()->subDays(4));
        $this->notify($this->masters['martins'], NotificationTypeEnum::PROPOSAL_RECEIVED,
            'Jauns pretpiedāvājums', 'Anna Riekstiņa nosūtīja pretpiedāvājumu 370 EUR.',
            $jobUrl('Balkona krāsošana - 2 kārtas', '/master/my-applications'), null);

        // Ilze (meistars)
        $this->notify($this->masters['ilze'], NotificationTypeEnum::APPLICATION_ACCEPTED,
            'Pieteikums pieņemts!', 'Tonijs Bāliņš pieņēmis jūsu pieteikumu "Pēcremonta tīrīšana".',
            $jobUrl('Pēcremonta tīrīšana dzīvoklī', '/master/my-applications'), $now->copy()->subDays(20));
        $this->notify($this->masters['ilze'], NotificationTypeEnum::JOB_PAID,
            'Darba apmaksa saņemta', 'Tonijs Bāliņš ir apmaksājis darbu.',
            $jobUrl('Pēcremonta tīrīšana dzīvoklī', '/master/my-applications'), $now->copy()->subDays(18));

        // Roberts (meistars)
        $this->notify($this->masters['roberts'], NotificationTypeEnum::NEW_REVIEW,
            'Jauna atsauksme!', 'Tonijs Bāliņš atstāja 5 zvaigžņu atsauksmi par jūsu darbu.',
            '/meistars/' . $this->masters['roberts']->getId(), $now->copy()->subDays(28));
        $this->notify($this->masters['roberts'], NotificationTypeEnum::APPLICATION_SHORTLISTED,
            'Jūsu pieteikums apsvērts', 'Gunārs Bērziņš apsvēra jūsu pieteikumu "Koka žogs".',
            $jobUrl('Koka žogs apkārt dārzam', '/master/my-applications'), $now->copy()->subDays(5));

        // Linda (meistars)
        $this->notify($this->masters['linda'], NotificationTypeEnum::NEW_REVIEW,
            'Jauna atsauksme!', 'Gunārs Bērziņš atstāja 5 zvaigžņu atsauksmi.',
            '/meistars/' . $this->masters['linda']->getId(), $now->copy()->subDays(48));
        $this->notify($this->masters['linda'], NotificationTypeEnum::APPLICATION_ACCEPTED,
            'Pieteikums pieņemts!', 'Jānis Auziņš pieņēmis jūsu pieteikumu "Koka dekoratīvais žogs".',
            $jobUrl('Koka dekoratīvais žogs terasei', '/master/my-applications'), $now->copy()->subDays(14));
        $this->notify($this->masters['linda'], NotificationTypeEnum::JOB_DISPUTED,
            'Strīds iesākts', 'Kārlis Dārziņš iesācis strīdu par darbu "Dārza labiekārtošana".',
            $jobUrl('Dārza labiekārtošana ar stādījumiem', '/seeker/my-requests'),
            null);

        // Tonijs (darba meklētājs)
        $this->notify($this->seekers['tonijs'], NotificationTypeEnum::NEW_APPLICATION,
            'Jauns pieteikums', 'Andrejs Bērziņš pieteicās jūsu darbam "Elektroinstalācija viesistabā".',
            $jobUrl('Elektroinstalācija viesistabā', '/seeker/my-requests'), $now->copy()->subDays(3));
        $this->notify($this->seekers['tonijs'], NotificationTypeEnum::JOB_MARKED_COMPLETE,
            'Darbs atzīmēts kā pabeigts', 'Ilze Liepiņa atzīmēja darbu "Pēcremonta tīrīšana" kā pabeigtu.',
            $jobUrl('Pēcremonta tīrīšana dzīvoklī', '/seeker/my-requests'), null);

        // Anna (darba meklētāja)
        $this->notify($this->seekers['anna'], NotificationTypeEnum::NEW_APPLICATION,
            'Jauns pieteikums', 'Jānis Auziņš pieteicās jūsu darbam "Vannas istabas flīžu nomaiņa".',
            $jobUrl('Vannas istabas flīžu nomaiņa', '/seeker/my-requests'), $now->copy()->subDays(1));
        $this->notify($this->seekers['anna'], NotificationTypeEnum::PROPOSAL_RECEIVED,
            'Jauns cenu piedāvājums', 'Mārtiņš Ozols nosūtīja pretpiedāvājumu 380 EUR.',
            $jobUrl('Balkona krāsošana - 2 kārtas', '/seeker/my-requests'), $now->copy()->subDays(1));
        $this->notify($this->seekers['anna'], NotificationTypeEnum::NEW_REVIEW,
            'Jūsu darbs novērtēts', 'Atsauksme pievienota par pabeigto biroja tīrīšanu.',
            '/seeker/my-requests', $now->copy()->subDays(33));

        // Kārlis (darba meklētājs)
        $this->notify($this->seekers['karlis'], NotificationTypeEnum::JOB_DISPUTED,
            'Strīda pieteikums nosūtīts', 'Jūsu strīds par darbu ir iesniegts un tiek izskatīts.',
            $jobUrl('Dārza labiekārtošana ar stādījumiem', '/seeker/my-requests'), $now->copy()->subDays(10));
        $this->notify($this->seekers['karlis'], NotificationTypeEnum::CATEGORY_SUGGESTION_REJECTED,
            'Kategorijas ieteikums noraidīts', 'Jūsu ieteikums "Auto mazgāšana" ir noraidīts.',
            '/seeker/categories', $now->copy()->subDays(20));

        // Gunārs (darba meklētājs)
        $this->notify($this->seekers['gunars'], NotificationTypeEnum::JOB_MARKED_COMPLETE,
            'Darbs atzīmēts kā pabeigts', 'Kristīne Saulīte atzīmēja darbu "Logu mazgāšana" kā pabeigtu. Lūdzu apstipriniet.',
            $jobUrl('Logu mazgāšana daudzdzīvokļu mājā', '/seeker/my-requests'), null);
        $this->notify($this->seekers['gunars'], NotificationTypeEnum::PROPOSAL_RECEIVED,
            'Jauns cenu piedāvājums', 'Roberts Krūmiņš nosūtīja pretpiedāvājumu 1350 EUR par žogu.',
            $jobUrl('Koka žogs apkārt dārzam', '/seeker/my-requests'), null);
    }

    private function notify(User $user, NotificationTypeEnum $type, string $title,
        ?string $body, ?string $url = null, ?Carbon $readAt = null): void
    {
        Notification::create([
            Notification::USER_ID => $user->getId(),
            Notification::TYPE => $type,
            Notification::TITLE => $title,
            Notification::BODY => $body,
            Notification::ACTION_URL => $url,
            Notification::READ_AT => $readAt,
        ]);
    }
}
