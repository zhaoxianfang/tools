<?php

namespace zxf\Laravel\Modules\Constants;

class ModuleEvent
{
    const BOOT = 'boot';

    const REGISTER = 'register';

    /**
     * @deprecated 废弃
     */
    const DISABLING = 'disabling';

    /**
     * @deprecated 废弃
     */
    const DISABLED = 'disabled';

    /**
     * @deprecated 废弃
     */
    const ENABLING = 'enabling';

    /**
     * @deprecated 废弃
     */
    const ENABLED = 'enabled';

    const CREATING = 'creating';

    const CREATED = 'created';

    const DELETING = 'deleting';

    const DELETED = 'deleted';
}
