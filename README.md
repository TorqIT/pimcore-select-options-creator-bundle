# pimcore-select-options-creator-bundle
This bundle will create select options based on the generated .yaml file.

# Installing the package via composer

This bundle is easily installed via composer: `composer require torqit/pimcore-select-options-creator-bundle`

Then add the bundle to your `Kernel.php` file:
```
        if (class_exists("TorqIT\\SelectOptionsCreatorBundle\\SelectOptionsCreatorBundle")) {
            $collection->addBundle(new SelectOptionsCreatorBundle());
        }
```

# Steps to create a grid layout on a DataObject:

1. Create `select_options.yaml` under the config directory. View the example .yaml file in the repo.
2. Run the generator command by: `bin/console torq:generate-select-options`. You can force re-create the options by adding the `--force-recreate-options` option.

