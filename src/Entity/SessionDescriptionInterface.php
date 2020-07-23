<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\DescribableEntityInterface;
use App\Traits\IdentifiableEntityInterface;

/**
 * Interface SessionDescriptionInterface
 * @deprecated
 */
interface SessionDescriptionInterface extends
    DescribableEntityInterface,
    IdentifiableEntityInterface,
    LoggableEntityInterface,
    SessionStampableInterface
{
    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session);

    /**
     * @return SessionInterface|null
     */
    public function getSession();
}
