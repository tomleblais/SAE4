<?php

namespace App\Models;

use Database\Factories\AutorisationsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Stores a part of the day.
 * @property int AUT_personne
 * @property boolean AUT_directeur_section
 * @property boolean AUT_securite_surface
 * @property boolean AUT_pilote
 * @property boolean AUT_secretaire
 * @method static Autorisations find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op=null, $value=null)
 * @method static AutorisationsFactory factory(...$parameters)
 */
class Autorisations extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_AUTORISATIONS';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'AUT_personne';

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
    protected $fillable = ['AUT_directeur_section', 'AUT_securite_surface', 'AUT_pilote', 'AUT_secretaire'];


    public function toArray(): array
    {
        return [
            "id" => $this->AUT_personne,
            "directeur_de_section" => $this->AUT_directeur_section,
            "securite_de_surface" => $this->AUT_securite_surface,
            "pilote" => $this->AUT_pilote,
            "secretaire" => $this->AUT_secretaire
        ];
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'AUT_directeur_section' => 'boolean',
        'AUT_securite_surface' => 'boolean',
        'AUT_pilote' => 'boolean',
        'AUT_secretaire' => 'boolean',
    ];

    public function personne(): BelongsTo
    {
        return $this->belongsTo( Personne::class, "AUT_personne", 'PER_id');
    }
}
