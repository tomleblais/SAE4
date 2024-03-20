<?php

namespace Database\Seeders;

use App\Models\Adherent;
use App\Models\Autorisations;
use App\Models\Etat;
use App\Models\Niveau;
use App\Models\Palanquee;
use App\Models\Participe;
use App\Models\Plongee;
use DateInterval;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Mockery\Exception;

class TestPlongeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception if DateInterval is not correct
     */
    public static function run($nbDives=10, $nbDivers=50, $active=null, $minDivers=0, $minLevel=0, $etat=false)
    {
        if (Autorisations::count('*') < 4)
            TestPersonneSeeder::run($nbDivers);
        $state = [
            'PLO_pilote' => TestPersonneSeeder::$pilot,
            'PLO_securite' => TestPersonneSeeder::$security];
        if (isset($active)) $state['PLO_active'] = $active;

        $plongees = Plongee::factory()->count($nbDives)->state($state)->minLevel($minLevel)->create();

        foreach ($plongees as /** @var Plongee $dive*/ $dive) {
            $nb = rand($minDivers, $dive->PLO_max_plongeurs);

            //printf("\nDive id=%d -> %d divers possible: ",$dive->PLO_id, $nb);
            /** @var Collection $members */
            $members= Adherent::where('ADH_niveau', '>=', $dive->PLO_niveau)->get();
            if (count($members) > $nb) {
                $members = $members->shuffle()->take($nb);
            }

            foreach ($members as /** @var Adherent $member*/ $member) {
                Participe::create(['PAR_plongee' => $dive->PLO_id, 'PAR_adherent' => $member->ADH_id])->save();
            }
            //printf("keeping %d\n", $members->count());

            /** @var Niveau $level */
            $level = $dive->niveau()->first();
            $members = $members->sortBy('ADH_niveau')->values()->all();
            $min=0;
            $max=count($members);
            $nbPalanquees = ceil($max / 4);
            $p = 0;
            while ($min < $max) {
                $palanquee = Palanquee::create([
                    'PAL_plongee' => $dive->PLO_id,
                    'PAL_max_prof' => rand(3, $level->getMaxDepth()),
                    'PAL_max_duree' => rand(15,45),
                    ]);
                $palanquee->save();
                $nb = ceil(($max-$min) / (0.+$nbPalanquees-$p));
                $insertLow = true;
                //printf("- Palanquee %d/%d : %d divers\n", $p, $nbPalanquees, $nb);
                //printf(" (min=%d max=%d => nb=%f)\n",$min, $max, (($max-$min) / (0.+$nbPalanquees-$p)));
                while ($nb>0) {
                    if ($insertLow) {
                        //printf("  + min=%d niv=%d/%d\n",$min, $members[$min]->ADH_niveau, $members[$min]->ADH_niveau);
                        $palanquee->members()->create(['INC_palanquee' => $palanquee->PAL_id, 'INC_adherent' => $members[$min]->ADH_id])->save();
                        ++$min;
                    } else {
                        --$max;
                        //printf("  + max=%d niv=%d/%d\n",$max,$members[$max]->ADH_niveau, $members[$max]->ADH_niveau);
                        $palanquee->members()->create(['INC_palanquee' => $palanquee->PAL_id, 'INC_adherent' => $members[$max]->ADH_id])->save();
                    }
                    $insertLow = ! $insertLow;
                    --$nb;
                }
                ++$p;
            }
            if (count($dive->getPossibleStates())>2 && rand(1,2)==1) { //Created, Parameterized or Cancelled
                $dive->PLO_etat = Etat::$PARAMETERIZED;
                foreach ($dive->palanquees()->get() as /**  @var Palanquee $palanquee */ $palanquee) {
                    if (rand(1,3)<3) {
                        $palanquee->PAL_prof_realisee = rand($palanquee->PAL_max_prof / 2, $palanquee->PAL_max_prof);
                        $palanquee->PAL_duree_realisee = rand($palanquee->PAL_max_duree * 0.6, $palanquee->PAL_max_duree * 1.2);
                        switch ($dive->PLO_moment) {
                            case 1:
                                $beginning = new DateTime('9:00');
                                break;
                            case 2:
                                $beginning = new DateTime('14:30');
                                break;
                            case 3:
                                $beginning = new DateTime('20:15');
                                break;
                            default:
                                throw new Exception("Unknown moment!");
                        }
                        $palanquee->PAL_heure_immersion = $beginning->add(new DateInterval("PT" . rand(0, 90) . "M"));
                        $palanquee->PAL_heure_sortie = $palanquee->PAL_heure_immersion->add(
                            new DateInterval("PT" . $palanquee->PAL_duree_realisee . "M"));
                        $palanquee->save();
                    }



                    if (count($dive->getPossibleStates()) > 2 && rand(1, 20) < 15) { //Parametrized, Validated or Cancelled
                        $dive->PLO_etat = Etat::$VALIDATED;
                        if (now()->diff($dive->PLO_date)->y <= 1)
                            $dive->PLO_etat = Etat::$OLD;
                    }
                }
            }
            if (rand(1,100)<=5)
                $dive->PLO_etat = Etat::$CANCELLED;
            
            if($etat)
                $dive->PLO_etat = Etat::$CREATED;
            
            $dive->save();
        }
    }
}
