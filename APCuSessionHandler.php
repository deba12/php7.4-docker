<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

class APCuSessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface {

    private int $ttl;

    /**
     * @param int $ttl
     */
    public function __construct(int $ttl) {
        $this->ttl = $ttl;
    }

    /**
     * @param $path
     * @param $name
     * @return bool
     */
    public function open($path, $name): bool {
        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool {
        return true;
    }

    /**
     * @param $id
     * @return false|string
     */
    public function read($id): false|string {
        return apcu_fetch($id);
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     */
    public function write($id, $data): bool {
        return apcu_store($id, $data, $this->ttl);
    }

    /**
     * @param $id
     * @return bool
     */
    public function destroy($id): bool {
        return apcu_delete($id);
    }

    /**
     * @param $max_lifetime
     * @return int|false
     */
    public function gc($max_lifetime): int|false {
        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function create_sid(): string {
        return bin2hex(random_bytes(16));
    }

    /**
     * @param $id
     * @return bool
     */
    public function validateId($id): bool {
        return (bool)preg_match('/^[0-9a-f]{32}$/', $id);
    }

    /**
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateTimestamp($id, $data): bool {
        return true;
    }

}

$enabled = getenv('APCU');

if(!empty($enabled)) {

    $ttl = getenv('APCU_TTL');

    if(is_numeric($ttl)) {
        $ttl = (int)$ttl;
    } else {
        $ttl = 1800;
    }

    $handler = new APCuSessionHandler($ttl);
    session_set_save_handler($handler, true);

}