@managing_products
Feature: Exporting products to csv file via cli
    In order to have my products exported to an external target
    As a developer
    I want to be able to export products data to csv file from the command line

    Background:
        Given I have a working command-line interface

    @cli_importer_exporter
    Scenario: Exporting countries to csv-file
        When I export "product" data as "csv" to the file "products_export.csv" with the cli-command
        Then I should see "Exported" in the output
