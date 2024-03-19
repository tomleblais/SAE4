<?php

namespace App\Models;

use Database\Factories\LieuFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores a location for diving.
 * @property int $LIE_id
 * @property string $LIE_libelle
 * @property string $LIE_description
 * @property boolean $LIE_active
 * @method static Lieu find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 * @method static LieuFactory factory(...$parameters)
 */
class Lieu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_LIEUX';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'LIE_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'LIE_active' => 'boolean'
    ];

    public function toArray(): array
    {
        return [
            "id" => $this->LIE_id,
            "libelle" => $this->LIE_libelle,
            "description" => $this->LIE_description,
            'actif'=>$this->LIE_active
        ];
    }

    /** Gets the unique id for selecting in views. */
    public function getId() : int {
        return $this->LIE_id;
    }

    /** Gets the text to display when selecting in views. */
    public function getText() : string {
        return $this->LIE_libelle;
    }

    public static function active() {
        return Lieu::all()->where('LIE_active', '1');
    }
}
