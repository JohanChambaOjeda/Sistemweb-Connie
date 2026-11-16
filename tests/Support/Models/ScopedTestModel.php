<?php

namespace Tests\Support\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

/**
 * Only used by the tests that cover the BelongsToCompany trait itself.
 */
class ScopedTestModel extends Model
{
    use BelongsToCompany;

    protected $table = 'scoped_test_models';

    /** @var list<string> */
    protected $fillable = ['name', 'company_id'];
}
