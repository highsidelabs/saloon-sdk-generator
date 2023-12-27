<?php

namespace Crescat\SaloonSdkGenerator;

use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Traits\Responses\HasResponse;

abstract class BaseResponse extends BaseDto implements WithResponse
{
    use HasResponse;
}
