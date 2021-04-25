<?php

namespace MenAtWork\SyncCto\Clients;

/**
 * Trait TraitClient
 *
 * @package MenAtWork\SyncCto\Clients
 *
 * @see \MenAtWork\SyncCto\Clients\IClient
 */
trait TraitClient
{
    /**
     * Title of the client.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Set the title of this client. Something like "Remote: Server @ dida".
     *
     * @param string $title
     *
     * @return IClient
     */
    public function setTitle(string $title): IClient
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the title of this client.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
