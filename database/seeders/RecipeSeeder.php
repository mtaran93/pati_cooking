<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Mews\Purifier\Facades\Purifier;

class RecipeSeeder extends Seeder
{
    /**
     * The 6 sample recipes come verbatim from design/CookingSite.dc.html (data[]).
     * Each `steps` array is rendered to an <ol> for `description` (the method) and
     * run through the same purifier used on admin save. Calories has no source in
     * the design data, so each value below is a realistic per-serving estimate the
     * admin can correct later.
     */
    public function run(): void
    {
        foreach ($this->recipes() as $data) {
            $steps = $data['steps'];
            unset($data['steps']);

            // Resolve [category, subcategory] pairs to Subcategory ids (self-sufficient
            // if the Category/Subcategory seeders were skipped).
            $subcategoryIds = array_map(
                fn (array $pair): int => Subcategory::firstOrCreate([
                    'category_id' => Category::firstOrCreate(['name' => $pair[0]])->id,
                    'name' => $pair[1],
                ])->id,
                $data['subcategories']
            );
            unset($data['subcategories']);

            $data['description'] = Purifier::clean($this->stepsToHtml($steps));

            $recipe = Recipe::updateOrCreate(['slug' => $data['slug']], $data);
            $recipe->subcategories()->sync($subcategoryIds);
        }
    }

    private function stepsToHtml(array $steps): string
    {
        $items = array_map(
            fn (string $s): string => '<li>'.e($s).'</li>',
            $steps
        );

        return '<ol>'.implode('', $items).'</ol>';
    }

    private function recipes(): array
    {
        return [
            [
                'slug' => 'bruschetta-con-avocado',
                'subcategories' => [['Aperitive', 'Bruschete']],
                'title' => 'Bruschetta con avocado',
                'time_label' => '15 min',
                'servings' => 6,
                'difficulty' => 'Ușor',
                'calories' => 240,
                'note' => 'Bunica mea nu a văzut un avocado în viața ei. I-ar fi plăcut și ar fi negat public.',
                'blurb' => 'Semnătura casei: pâine la grătar frecată cu usturoi, avocado copt în locul obișnuit al roșiilor și ulei bun peste tot.',
                'ingredients' => ['1 pâine țărănească, felii groase', '2 avocado bine copți', '1 cățel de usturoi', '12 roșii cherry, tăiate în jumătate', 'Ulei de măsline extravirgin', 'Sare în fulgi și busuioc'],
                'steps' => ['Prăjește pâinea până se rumenește ușor pe margini, apoi freacă fiecare felie o dată cu usturoiul tăiat.', 'Zdrobește avocado cu furculița — textură, nu piure — și asezonează bine cu sare.', 'Întinde avocado pe pâinea caldă și adaugă roșiile.', 'Termină cu busuioc rupt, un fir generos de ulei de măsline și sare în fulgi. Servește imediat, cât pâinea e încă crocantă.'],
            ],
            [
                'slug' => 'spaghetti-alla-carbonara',
                'subcategories' => [['Fel principal', 'Paste']],
                'title' => 'Spaghetti alla carbonara',
                'time_label' => '25 min',
                'servings' => 4,
                'difficulty' => 'Ușor',
                'calories' => 640,
                'note' => 'Dacă pui smântână, să nu-mi spui. Dacă îmi spui, să nu te mai întorci.',
                'blurb' => 'Ouă, guanciale, pecorino și piper. Nimic altceva — sosul se face din căldură și răbdare, nu din smântână.',
                'ingredients' => ['400 g spaghete', '150 g guanciale, tăiat fâșii', '4 gălbenușuri + 1 ou întreg', '80 g Pecorino Romano, ras', 'Piper negru, măcinat mare', 'Sare pentru apa de paste'],
                'steps' => ['Topește guanciale la foc mic, pornind din tigaie rece, până devine auriu și crocant; dă-l deoparte.', 'Bate gălbenușurile, oul întreg, pecorino și mult piper până obții o pastă groasă.', 'Fierbe spaghetele în apă bine sărată; păstrează o cană din apa cu amidon.', 'Amestecă pastele cu guanciale, apoi — luate de pe foc — cu amestecul de ou, subțiind cu apă de paste până îmbracă totul ca mătasea.', 'Servește imediat, cu încă pecorino și piper. Smântâna nu a intrat niciodată în această rețetă și nici nu va intra.'],
            ],
            [
                'slug' => 'risotto-alla-milanese',
                'subcategories' => [['Fel principal', 'Risotto']],
                'title' => 'Risotto alla milanese',
                'time_label' => '40 min',
                'servings' => 4,
                'difficulty' => 'Mediu',
                'calories' => 560,
                'note' => 'Amestecatul risotto-ului nu e o corvoadă — sunt douăzeci de minute în care nimeni nu are voie să-ți ceară nimic.',
                'blurb' => 'Risotto auriu cu șofran, amestecat până curge în val lent, terminat cu unt și Parmigiano, așa cum insistă Milano.',
                'ingredients' => ['320 g orez Carnaroli', '1 ceapă mică, tocată', '1,2 l supă fierbinte', '1 vârf de șofran', '100 ml vin alb sec', '60 g unt + 60 g Parmigiano'],
                'steps' => ['Călește ceapa în jumătate din unt, fără să se coloreze.', 'Prăjește orezul două minute, până boabele devin translucide pe margini.', 'Toarnă vinul și lasă-l să se evapore complet.', 'Adaugă supa fierbinte polonic cu polonic, amestecând și lăsând-o să se absoarbă. Înmoaie șofranul într-un polonic și adaugă-l la jumătate.', 'La optsprezece minute, luat de pe foc: încorporează restul de unt și Parmigiano până risotto-ul cade în val lent. Lasă un minut, apoi servește.'],
            ],
            [
                'slug' => 'tagliatelle-al-ragu',
                'subcategories' => [['Fel principal', 'Paste']],
                'title' => 'Tagliatelle al ragù',
                'time_label' => '3 ore',
                'servings' => 6,
                'difficulty' => 'Mediu',
                'calories' => 720,
                'note' => 'Trei ore nu e timpul de gătire. E pedeapsa minimă.',
                'blurb' => 'Un ragù de duminică în stil bolognez — soffritto, două cărnuri, lapte și timp. Oala face aproape toată treaba.',
                'ingredients' => ['500 g tagliatelle proaspete', '300 g vită + 200 g porc, tocate mare', '1 morcov, 1 tijă de țelină, 1 ceapă', '150 ml vin alb', '400 g passata de roșii', '150 ml lapte integral'],
                'steps' => ['Călește morcovul, țelina și ceapa tocate în ulei de măsline până devin dulci și moi — nu grăbi pasul acesta.', 'Dă focul mare, adaugă carnea și rumenește-o bine, sfărâmând-o pe măsură ce se face.', 'Stinge cu vinul; când s-a dus, adaugă passata și o cană de apă.', 'Fierbe la foc abia mișcat două ore și jumătate, adăugând laptele în ultimele treizeci de minute.', 'Amestecă cu tagliatelle proaspete și o lingură din apa de paste. Parmigiano la masă.'],
            ],
            [
                'slug' => 'caprese-con-avocado',
                'subcategories' => [['Salate', 'Reci'], ['Aperitive', 'Reci']],
                'title' => 'Caprese con avocado',
                'time_label' => '10 min',
                'servings' => 4,
                'difficulty' => 'Ușor',
                'calories' => 320,
                'note' => 'O salată cu trei ingrediente nu iartă nimic. Cumpără mozzarella bună.',
                'blurb' => 'Mozzarella, roșii și busuioc, cu felii de avocado printre ele — o mică libertate modernă pe care oaspeții o tot cer.',
                'ingredients' => ['2 roșii mari, coapte', '250 g mozzarella di bufala', '1 avocado copt', 'Frunze de busuioc proaspăt', 'Ulei de măsline extravirgin', 'Sare în fulgi și piper negru'],
                'steps' => ['Taie roșiile, mozzarella și avocado la aceeași grosime.', 'Așază-le pe o farfurie rece, alternând — roșie, mozzarella, avocado, busuioc.', 'Asezonează cu sare și piper doar în ultimul moment, ca roșiile să nu lase zeamă.', 'Îmbracă totul în cel mai bun ulei de măsline și servește în cel mult un sfert de oră.'],
            ],
            [
                'slug' => 'tiramisu',
                'subcategories' => [['Desert', 'Cremoase']],
                'title' => 'Tiramisù',
                'time_label' => '30 min + 4 ore repaus',
                'servings' => 8,
                'difficulty' => 'Mediu',
                'calories' => 450,
                'note' => 'Peste noapte e mai bun. Nimeni nu a așteptat vreodată peste noapte.',
                'blurb' => 'Clasicul care te ridică: pișcoturi însiropate în espresso sub nori de mascarpone, lăsat peste noapte dacă ai răbdare.',
                'ingredients' => ['300 g pișcoturi savoiardi', '500 g mascarpone', '4 ouă, separate', '100 g zahăr', '300 ml espresso tare, răcit', 'Cacao neîndulcită la final'],
                'steps' => ['Bate gălbenușurile cu zahărul până devin deschise la culoare, apoi încorporează mascarpone.', 'Bate albușurile spumă moale și încorporează-le în două tranșe.', 'Înmoaie fiecare pișcot scurt în espresso — o secundă pe fiecare parte, nu mai mult.', 'Așază două rânduri de pișcoturi și cremă într-un vas, terminând cu cremă.', 'Lasă la rece cel puțin patru ore — peste noapte e și mai bine — și pudrează gros cu cacao înainte de servire.'],
            ],
        ];
    }
}
