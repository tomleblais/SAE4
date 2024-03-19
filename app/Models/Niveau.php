<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Collection\Collection;

/** Stores a diving Level.
 * @property int $NIV_id
 * @property string $NIV_code
 * @property string $NIV_libelle
 * @property int $NIV_prof_encadre
 * @property int $NIV_prof_autonome
 * @property int $NIV_niveau
 * @property boolean $NIV_guide
 * @property boolean $NIV_directeur
 * @property Collection $adherents
 * @method static Niveau find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 */
class Niveau extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_NIVEAUX';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'NIV_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        return [
            "id" => $this->NIV_id,
            "libelle" => $this->NIV_libelle,
            "code" => $this->NIV_code,
            "profondeur_si_encadre" => $this->NIV_prof_encadre,
            "profondeur_si_autonome" => $this->NIV_prof_autonome,
            "niveau" => $this->NIV_niveau,
            "guide_de_palanquee" => $this->NIV_guide,
            "directeur_de_plongee" => $this->NIV_directeur
        ];
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'NIV_guide' => 'boolean',
        'NIV_directeur' => 'boolean',
    ];

    // Relationships

    public function adherents(): HasMany
    {
        return $this->hasMany(Adherent::class, "ADH_niveau");
    }

    /** Gets the unique id for selecting in views. */
    public function getId() : int {
        return $this->NIV_id;
    }

    /** Gets the text to display when selecting in views. */
    public function getText() : string {
        return $this->NIV_libelle . ' (' . $this->NIV_code . ')';
    }

    /** Gets the maximal depth reachable at this level.
     * @return int the max of autonomous depth and tutored depth
     */
    public function getMaxDepth(): int
    {
        return max($this->NIV_prof_autonome, $this->NIV_prof_encadre);
    }
}
