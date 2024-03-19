<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Stores a part of the day.
 * @property int $MOM_id
 * @property string $MOM_libelle
 * @method static Moment find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 */
class Moment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_MOMENTS';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'MOM_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        return [
            "id" => $this->MOM_id,
            "libelle" => $this->MOM_libelle
        ];
    }

    /** Gets the unique id for selecting in views. */
    public function getId() : int {
        return $this->MOM_id;
    }

    /** Gets the text to display when selecting in views. */
    public function getText() : string {
        return $this->MOM_libelle;
    }

}
