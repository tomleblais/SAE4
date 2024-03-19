<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Stores a palanquee, that is a group of a divers.
 * @property int $PAL_id
 * @property int $PAL_plongee
 * @property int $PAL_max_prof
 * @property int $PAL_max_duree
 * @property DateTime $PAL_heure_immersion
 * @property DateTime $PAL_heure_sortie
 * @property int $PAL_prof_realisee
 * @property int $PAL_duree_realisee
 * @property Plongee $plongee
 * @property Collection $members
 * @method static Palanquee find(int $id)
 * @method static Palanquee first()
 * @method static Palanquee create(array $fillable)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 */
class Palanquee extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_PALANQUEES';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'PAL_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['PAL_plongee', 'PAL_max_prof', 'PAL_max_duree'];

    public function toArray(): array
    {
        $res = [
            'id' => $this->PAL_id,
            'plongee' => $this->PAL_plongee,
            'max_profondeur' => $this->PAL_max_prof,
            'max_duree' => $this->PAL_max_duree,
            'heure_immersion' => $this->getImmersion(),
            'heure_sortie' => $this->getSortie(),
            'profondeur_realisee' => $this->PAL_prof_realisee,
            'duree_realisee' => $this->PAL_duree_realisee
        ];
        if ($this->relationLoaded("plongee"))
            return array_merge($res, [
                "plongee_niveau" => $this->plongee->niveau->NIV_code,
                "plongee_etat" => $this->plongee->etat->ETA_libelle
            ]);
        return $res;
    }

    /** Gets the immersion time in the format "H:i"
     * 
     * @return string|null
     */
    public function getImmersion() : ?string {
        $time = $this->PAL_heure_immersion;
        if ($time == null) return null;
        return $time->format("H:i");
    }

    /** Gets the exit time in the format "H:i"
     * 
     * @return string|null
     */
    public function getSortie() : ?string {
        $time = $this->PAL_heure_sortie;
        if ($time == null) return null;
        return $time->format("H:i");
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'PAL_heure_immersion' => 'datetime',
        'PAL_heure_sortie' => 'datetime',
    ];

    // Relationships

    /** Gets the dive where is the Palanquee
     * 
     * @return BelongsTo
     */
    public function plongee(): BelongsTo
    {
        return $this->belongsTo(Plongee::class, "PAL_plongee", "PLO_id");
    }

    /** Gets the members of the Palanquee
     * 
     * @return HasMany
     */
    public function members(): HasMany
    {
        return $this->hasMany(Inclut::class, "INC_palanquee", "PAL_id");
    }

    /** Gets the unique id for selecting in views. */
    public function isOk() : bool {
        //Log::debug("Testing Palanquee $this->PAL_id of Dive ".$this->PAL_plongee);
        $this->loadMissing('members.adherent.niveau');
        $members = $this->members;
        $count = $members->count();
        if ($count < 2) {
            //Log::debug("Palanquee!Ok: less than 2");
            return false;
        } // never dive alone
        $encadre=100;
        $encadrement=0;
        $group=5;
        foreach ($members as /** @var Inclut $member */ $member) {
            if ($member->adherent->niveau->NIV_code == 'PB')
                $group = 2;
            elseif ($member->adherent->niveau->NIV_niveau < 10)
                $group = 5;
            if ($member->adherent->niveau->NIV_prof_encadre < $encadre
                && $member->adherent->niveau->NIV_prof_autonome < $this->PAL_max_prof)
                $encadre = $member->adherent->niveau->NIV_prof_encadre;
            elseif ($member->adherent->niveau->NIV_code == 'E1' && $encadrement<6)
                $encadrement = 6;
            elseif ($member->adherent->niveau->NIV_guide)
                $encadrement = 60;
        }
        if ($encadre<100 && $encadrement==0) {
            //Log::debug("Palanquee!Ok: need guide but no guide");
            return false;
        } // needs guide but no guide
        if ($encadre<100 && $count>$group) {
            //Log::debug("Palanquee!Ok: count($count)>group($group)");
            return false;
        } // bronze needs guide and nobody else, otherwise, guide may go with 4 divers
        if ($encadrement == 0 && $count>3) {
            //Log::debug("Palanquee!Ok: more than 3 autonomous divers");
            return false;
        } // up to 3 autonomous divers
        if ($encadrement > 0 && $count>5) {
            //Log::debug("Palanquee!Ok: more than 5 framed divers");
            return false;
        } // up to 3 autonomous divers
        if ($encadre<100 && $encadrement>0 && $encadrement<$this->PAL_max_prof) {
            //Log::debug("Palanquee!Ok: E1 guides down to 6m only");
            return false;
        } // E1 may guide only down to 6 meters
        foreach ($members as /** @var Inclut $member */ $member) {
            if ($member->adherent->niveau->NIV_prof_autonome < $this->PAL_max_prof
                && ($member->adherent->niveau->NIV_prof_encadre < $this->PAL_max_prof || $encadrement==0)) {
            //Log::debug("Palanquee!Ok: diver not autonomous enough($member->adherent->niveau->NIV_prof_autonome) or not framed enough ($member->adherent->niveau->NIV_prof_encadre) or not framed($encadrement)");
                return false; // not autonome enough, or not framed enough or not framed
            }
        }
        return true;
    }

    /** Gets the unique id for selecting in views. */
    public function isNotComplete() : bool {
        return ! ($this->isOk() && isset($this->PAL_duree_realisee) && isset($this->PAL_prof_realisee)
            && isset($this->PAL_heure_sortie) && isset($this->PAL_heure_immersion));
    }
}
