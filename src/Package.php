<?php

namespace ListLicenses;

class Package
{
    public string $name;
    public string $homepage;
    public string $description;
    public string $license;
    public string $version;
    public string $time;

    public function __construct(
        private readonly mixed $obj,
    ) {
            $this->name = $this->stringify($obj->name ?? "unknown");
            $this->homepage = $this->stringify($obj->homepage ?? "unknown");
            $this->description = $this->stringify($obj->description ?? "unknown");
            $this->license = $this->stringify(implode(", ", $obj->license ?? ["unknown"]));
            $this->version = $this->stringify($obj->version ?? "unknown");
            $this->time = $this->stringify($obj->time ?? "unknown");
    }

    private function stringify($obj)
    {
        return $obj;
    }
}
