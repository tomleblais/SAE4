<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Pivot table for associating Adherent to Palanquee
 * @property int $INC_id
 * @property int $INC_palanquee
 * @property int $INC_adherent
 * @property Adherent $adherent
 * @property Palanquee $palanquee
 * @method static Inclut find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 */
class Inclut extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_INCLUT';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'INC_id';

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
    protected $fillable = ['INC_palanquee', 'INC_adherent'];


    public function toArray(): array
    {
        $res = [
            "id" => $this->INC_id,
            "adherent" => $this->INC_adherent,
            "palanquee" => $this->INC_palanquee
        ];
        if ($this->relationLoaded("adherent"))
            $res = array_merge($res, [
                "adherent_nom" => $this->adherent->personne->PER_nom,
                "adherent_prenom" => $this->adherent->personne->PER_prenom,
                "adherent_niveau" => $this->adherent->niveau->NIV_code
            ]);
        if ($this->relationLoaded("palanquee"))
            $res = array_merge($res, [
                "palanquee_plongee" => $this->palanquee->PAL_plongee,
                "palanquee_max_prof" => $this->palanquee->PAL_max_prof,
                "palanquee_niveau" => $this->palanquee->plongee->niveau->NIV_code
            ]);
        return $res;
    }

    public function adherent(): BelongsTo
    {
        return $this->belongsTo(Adherent::class, "INC_adherent", "ADH_id");
    }

    public function palanquee(): BelongsTo
    {
        return $this->belongsTo(Palanquee::class, "INC_palanquee", "PAL_id");
    }

}
