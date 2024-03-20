<?php

namespace App\Models;

use Database\Factories\BateauFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** Stores a ship for diving.
 * @property int $BAT_id
 * @property string $BAT_libelle
 * @property int $BAT_max_personnes
 * @property boolean $BAT_active
 * @method static Bateau find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 * @method static BateauFactory factory(...$parameters)
 */
class Bateau extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['BAT_libelle', 'BAT_max_personnes'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_BATEAUX';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'BAT_id';

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
        'BAT_active' => 'boolean'
    ];

    /**
     * Retrieve all models from the database.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function toArray(): array
    {
        return [
          "id"=>$this->BAT_id,
          "libelle"=>$this->BAT_libelle,
          "max_personnes"=>$this->BAT_max_personnes,
          'actif'=>$this->BAT_active
        ];
    }

    /** Gets the unique id for selecting in views. */
    public function getId() : int {
        return $this->BAT_id;
    }

    /** Gets the text to display when selecting in views. */
    public function getText() : string {
        return $this->BAT_libelle;
    }
    
    /** Gets the description to display when selecting in views. */
    public static function active() {
        return Bateau::all()->where('BAT_active', '1');
    }

}
