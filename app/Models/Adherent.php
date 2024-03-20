<?php

namespace App\Models;

use Database\Factories\AutorisationsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * Class Adherent
 *
 * @package App\Models
 *
 * @property int $ADH_id
 * @property string $ADH_licence
 * @property DateTime $ADH_date_certificat
 * @property string $ADH_forfait
 * @property int $ADH_niveau
 * @property Personne $personne
 * @property Niveau $niveau
 *
 * @method static Adherent find(int $id)
 * @method static Builder where($col, $op=null, $value=null)
 */
class Adherent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ADH_id', 'ADH_licence', 'ADH_date_certificat', 'ADH_forfait', 'ADH_niveau'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_ADHERENTS';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'ADH_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Retrieve all models from the database.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function all($columns = ['*'])
    {
        return parent::with('personne')->get($columns);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $res = [
            "id" => $this->ADH_id,
            "licence" => $this->ADH_licence,
            "date_certificat_medical" => $this->ADH_date_certificat,
            "forfait" => $this->ADH_forfait,
            "niveau" => $this->ADH_niveau,
        ];
        if ($this->relationLoaded("niveau"))
            $res = array_merge($res, [
                "niveau_code" => $this->niveau->NIV_code,
                "profondeur_si_encadre" => $this->niveau->NIV_prof_encadre,
                "profondeur_si_autonome" => $this->niveau->NIV_prof_autonome,
                "niveau_libelle" => $this->niveau->NIV_libelle
            ]);
        if ($this->relationLoaded("personne"))
            $res = array_merge($res, [
                "nom" => $this->personne->PER_nom,
                "prenom" => $this->personne->PER_prenom,
                "email" => $this->personne->PER_email,
                'actif' => $this->personne->PER_active
            ]);
        return $res;
    }

    // Relationships

    /**
     * Get the niveau that belongs to the adherent.
     *
     * @return BelongsTo
     */
    public function niveau(): BelongsTo
    {
        return $this->belongsTo(Niveau::class, "ADH_niveau", "NIV_id");
    }

    /**
     * Get the personne that belongs to the adherent.
     *
     * @return BelongsTo
     */
    public function personne(): BelongsTo
    {
        return $this->belongsTo(Personne::class, "ADH_id", "PER_id");
    }

    /**
     * Get the ID of the adherent.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->ADH_id;
    }

    /**
     * Get the text representation of the adherent's person.
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->personne->getText();
    }
}