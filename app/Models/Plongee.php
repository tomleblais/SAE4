<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Stores a given dive.
 * @property int $PLO_id
 * @property int $PLO_lieu
 * @property int $PLO_bateau
 * @property DateTime $PLO_date
 * @property int $PLO_moment
 * @property int $PLO_min_plongeurs
 * @property int $PLO_max_plongeurs
 * @property int $PLO_niveau
 * @property boolean $PLO_active
 * @property int $PLO_etat
 * @property int $PLO_pilote
 * @property int $PLO_securite
 * @property int $PLO_directeur
 * @property Lieu $lieu
 * @property Bateau $bateau
 * @property Moment $moment
 * @property Niveau $niveau
 * @property Etat $etat
 * @property Personne $pilote
 * @property Personne $securite
 * @property Adherent $directeur
 * @property Collection|Adherent[] $participants
 * @property Collection|Palanquee[] $palanquees
 * @method static Plongee find(int $id)
 * @method static Plongee first()
 * @method static Builder where($col, $op=null, $value=null)
 * @method static int count($fields)
 */
class Plongee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_PLONGEES';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'PLO_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        $res = [
            "id" => $this->PLO_id,
            "lieu" => $this->PLO_lieu,
            "bateau" => $this->PLO_bateau,
            "date" => $this->getDate(),
            "moment" => $this->PLO_moment,
            "min_plongeurs" => $this->PLO_min_plongeurs,
            "max_plongeurs" => $this->PLO_max_plongeurs,
            "niveau" => $this->PLO_niveau,
            "active" => $this->PLO_active,
            "etat" => $this->PLO_etat,
            'pilote' => $this->PLO_pilote,
            'securite_de_surface' => $this->PLO_securite,
            'directeur_de_plongee' => $this->PLO_directeur
        ];
        if ($this->relationLoaded("etat"))
            $res["etat_libelle"] = $this->etat->ETA_libelle;
        if ($this->relationLoaded("moment"))
            $res["moment_libelle"] = $this->moment->MOM_libelle;
        if ($this->relationLoaded("bateau")) {
            $res["bateau_libelle"] = $this->bateau->BAT_libelle;
            $res["bateau_max_personnes"] = $this->bateau->BAT_max_personnes;
        }
        if ($this->relationLoaded("lieu")) {
            $res["lieu_libelle"] = $this->lieu->LIE_libelle;
            $res["lieu_description"] = $this->lieu->LIE_description;
        }
        if ($this->relationLoaded("niveau")) {
            $res["niveau_code"] = $this->niveau->NIV_code;
            $res["niveau_libelle"] = $this->niveau->NIV_libelle;
            $res["niveau_profondeur_autonome"] = $this->niveau->NIV_prof_autonome;
            $res["niveau_profondeur_encadre"] = $this->niveau->NIV_prof_encadre;
        }
        if ($this->relationLoaded("pilote")) {
            $res["pilote_nom"] = $this->pilote->PER_nom;
            $res["pilote_prenom"] = $this->pilote->PER_prenom;
        }
        if ($this->relationLoaded("securite")) {
            $res["securite_nom"] = $this->securite->PER_nom;
            $res["securite_prenom"] = $this->securite->PER_prenom;
        }
        if ($this->relationLoaded("directeur")) {
            $res["directeur_nom"] = $this->directeur->personne->PER_nom;
            $res["directeur_prenom"] = $this->directeur->personne->PER_prenom;
        }
        return $res;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'PLO_active' => 'boolean',
        'PLO_date' => 'date'
    ];

    public function bateau(): BelongsTo
    {
        return $this->belongsTo(Bateau::class, "PLO_bateau", "BAT_id");
    }

    public function etat(): BelongsTo
    {
        return $this->belongsTo(Etat::class, "PLO_etat", "ETA_id");
    }

    public function lieu(): BelongsTo
    {
        return $this->belongsTo(Lieu::class, "PLO_lieu", "LIE_id");
    }

    public function moment(): BelongsTo
    {
        return $this->belongsTo(Moment::class, "PLO_moment", "MOM_id");
    }

    public function niveau(): BelongsTo
    {
        return $this->belongsTo(Niveau::class, "PLO_niveau", "NIV_id");
    }

    public function participants(): HasManyThrough
    {
        return $this->hasManyThrough(Adherent::class, Participe::class, 'PAR_plongee', 'ADH_id', 'PLO_id', 'PAR_adherent');
    }

    public function palanquees(): HasMany
    {
        return $this->hasMany(Palanquee::class, "PAL_plongee", 'PLO_id');
    }

    public function pilote(): HasOne
    {
        return $this->hasOne(Personne::class, 'PER_id', 'PLO_pilote');
    }

    public function securite(): HasOne
    {
        return $this->hasOne(Personne::class, 'PER_id', 'PLO_securite');
    }

    public function directeur(): HasOne
    {
        return $this->hasOne(Adherent::class, 'ADH_id', 'PLO_directeur');
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->PLO_date->format('Y-m-d');
    }

    public function isCancelled() : bool {
        return false;
    }

    public function isLocked() : bool {
        return $this->PLO_etat > Etat::$PARAMETERIZED;
    }

    public function nbFreeSlots() : int {
        return $this->PLO_max_plongeurs - $this->participants()->count();
    }

    /**
     * @return array a set Etat[] containing all possible states for this dive in its current situation
     */
    public function getPossibleStates() : array {
        $res = [$this->etat];
        if ($this->PLO_etat == Etat::$CREATED) {
            $nb = $this->participants->count();
            if ($nb>=$this->PLO_min_plongeurs && $nb<=$this->PLO_max_plongeurs) {
                $ok = true;
                foreach ($this->palanquees as $palanquee) {
                    if ($palanquee->isOK())
                        $nb -= $palanquee->members()->count();
                    else {
                        $ok = false;
                        break;
                    }
                }
                //Log::debug("Created, ok=$ok, nb=$nb");
                if ($ok && $nb==0) //all palanquees are ok and all divers are in a palanquee
                    $res[] = Etat::find(Etat::$PARAMETERIZED);
            }
        } elseif ($this->PLO_etat == Etat::$PARAMETERIZED) {
            $ok = true;
            foreach ($this->palanquees as $palanquee) {
                if ($palanquee->isNotComplete()) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) //all palanquees have been completed
                $res[] = Etat::find(Etat::$VALIDATED);
        } elseif ($this->PLO_etat == Etat::$VALIDATED) {
            if (now()->diff($this->PLO_date)->y >= 1)
                $res[] = Etat::find(Etat::$OLD); // TODO automatic modification?
        } elseif ($this->PLO_etat == Etat::$OLD) {
            // Nothing more can be done
        }  elseif ($this->PLO_etat == Etat::$CANCELLED) {
            if($this->PLO_date > now())
                $res[] = Etat::find(Etat::$CREATED);
        }
        if ($this->PLO_etat < Etat::$VALIDATED)
            $res[] = Etat::find(Etat::$CANCELLED);
        return $res;
    }

}
