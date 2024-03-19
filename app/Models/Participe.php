<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/** Pivot table for associating Adherent (with Role) to Plongee.
 * @property int $PAR_id
 * @property int $PAR_plongee
 * @property int $PAR_adherent
 * @property Plongee $plongee
 * @property Adherent $adherent
 * @method static Participe find(int $id)
 * @method static Participe first()
 * @method static Participe create(array $fillable)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 */
class Participe extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_PARTICIPE';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'PAR_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        $res = [
            "id" => $this->PAR_id,
            "adherent" => $this->PAR_adherent,
            "plongee" => $this->PAR_plongee
        ];
        if ($this->relationLoaded("adherent"))
            $res = array_merge($res, [
                "adherent_nom" => $this->adherent->personne->PER_nom,
                "adherent_prenom" => $this->adherent->personne->PER_prenom,
                "adherent_niveau" => $this->adherent->niveau->NIV_code
            ]);
        if ($this->relationLoaded("plongee"))
            $res = array_merge($res, [
                "plongee_date" => $this->plongee->PLO_date,
                "plongee_moment" => $this->plongee->moment->MOM_libelle,
                "plongee_niveau" => $this->plongee->niveau->NIV_code
            ]);
        return $res;
    }

    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class, "PAR_adherent", "ADH_id")->with("personne");
    }

    public function plongee(): BelongsTo
    {
        return $this->belongsTo(Plongee::class, "PAR_plongee", "PLO_id")->with(['niveau', 'moment']);
    }
    //TODO check participant is not in 2 dives with same date+moment
}
