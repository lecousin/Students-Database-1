2013/07/??: Version 2.0.0b8
---------------------------

```
ADDED:     Presentation mode to router Twig filter, allows for different representations
FIXED:     Minor Descriptor tweaks
FIXED:     Inheritance was broken due to an erroneous merge
FIXED:     Ampersands in tag descriptions caused XSL based templates to fatal
REMOVED:   Installer is removed; proved too unreliable
```

2013/07/12: Version 2.0.0b7
---------------------------

```
FIXED:     Warning in browser console 'Viewport argument value "device-width;" for key "width" is invalid, and has
           been ignored. Note that ';' is not a separator in viewport values. The list should be comma-separated.'
FIXED:     VERSION file was missing from phar archive, causing it to fail
FIXED:     Elements with an @ignore tag were not filtered
FIXED:     Deprecated elements are now striken in the class overview
FIXED:     The @see reference was not shown and interpreted
FIXED:     The @uses reference was not shown and interpreted
FIXED:     Response type was not shown with magic methods
FIXED:     Arguments were not shown with magic methods
FIXED:     Type is not shown with magic properties
FIXED:     Magic methods were missing from sidebar
FIXED:     Coding standards issues
FIXED:     Several documentation issues
FIXED:     Windows error where the directory slashes were incorrectly presented.
FIXED:     When a file contains multiple errors, only the first is shown.
FIXED:     Generating a new template gave a fatal error
FIXED:     Generated templates were missing the transformation line for their structure.xml
FIXED:     Linking to functions
FIXED:     Linking to constants
FIXED:     Linking to properties
FIXED:     Linking to methods
FIXED:     Root elements with the same shortname and file but in a different namespace would overwrite the other.
ADDED:     New template 'xml' for outputting a pure XML representation of the project's structure
ADDED:     Update instructions to issue a PR against homebrew if the phar file updates:
           https://github.com/josegonzalez/homebrew-php/blob/master/Formula/phpdocumentor.rb
CHANGED:   Assembling of Tags to Descriptors was refactored to work the same way as other Descriptors.
CHANGED:   Properties won't emit an error regarding a missing summary if it has an @var tag with a description.
```

2013/06/23: Version 2.0.0b6
---------------------------

```
ADDED:     Travis configuration was changed to test against 5.5
FIXED:     Packages were not indexed and shown correctly.
FIXED:     @var descriptions were not shown as summary if the summary was absent for a property.
FIXED:     Added static label on a property in the responsive template.
FIXED:     Alignment of tags in table display.
FIXED:     Response information was missing from method description.
FIXED:     Sourcecode viewer in new-black template.
FIXED:     Magic methods are now shown and inherited in the class view for the responsive-twig template.
FIXED:     Magic properties are now shown and inherited in the class view for the responsive-twig template.
FIXED:     Markdown fencing in responsive and responsive-twig template now correctly indents code examples.
CHANGED:   Deep link should not be shown for members without location.
CHANGED:   phpDocumentor now sets the memory limit to -1 to prevent issues due to a limited memory usage.
CHANGED:   Bumped maximum recursion setting from 5.000 to 10.000 since errors were still reported.
REMOVED:   `/bin/parse.php` binary; its function is carried out by `phpdoc project:parse`.
REMOVED:   `/bin/transform.php` binary; its function is carried out by `phpdoc project:transform`.
```

2013/06/21: Version 2.0.0b5
---------------------------

```
FIXED:     Root namespace was named `global` in overviews
FIXED:     An empty `Global ('\')` entry pointed to a non-existing default.html in the index
FIXED:     Since tag now shows the version number
FIXED:     Fatal error when an interface's parent could not be resolved in this project
FIXED:     API Documentation menu remains empty
FIXED:     Interface parents now link to the rest of the documentation
FIXED:     Inheritance of methods, constants and properties was not correctly recognized; this is now fixed
FIXED:     When a method argument has a typehint but no @param tag then the typehint will be shown
FIXED      Fatal error in XSL based templates when an interface extends another
```

2013/06/16: Version 2.0.0b4
---------------------------

```
FIXED      Fatal error that occurs when a constant has an error
FIXED      Fatal error that occurs in certain cases with the getError() method
FIXED      Refactored Builder into Assemblers to reduce technical debt
CHANGED    Refactored ProjectDescriptor Builder to separate assembling from filtering and validating
CHANGED    Introduced Symfony Validator component for element validation
CHANGED    Introduced ZF2 Filter component to filter elements (for example with @internal)
```

2013/06/14: Version 2.0.0b3
---------------------------

```
FIXED      Using an @uses tag results in a fatal error during execution
FIXED      Errors are now shown on the errors report of the responsive-twig template
FIXED      The error count on the index page of the responsive-twig template is restored
FIXED      Checkstyle output now functions as expected
FIXED      new-black template failed due to a capitalization issue with the Sourcecode writer
FIXED      Updated all templates to generate a structure.xml
FIXED      Fatal error when a property, constant or method collection contains a null value
FIXED      Fixed several errors in the Old Ocean template
FIXED      Removed broken logging call from Xsl writer
FIXED      Several PHP notices
```

2013/06/09: Version 2.0.0b2
---------------------------

```
FIXED      Package could not be set for constants, resulting in fatal error
FIXED      Default template was still responsive and not responsive-twig
```

2013/06/09: Version 2.0.0b1
---------------------------

The 13th alpha of phpDocumentor contains the final functionality for version 2.0.
A lot of effort has been put in making sure that the API, Object structure and plugin mechanism will remain
backwards compatible until version 3.0.

Features have been changed, removed and added; making this an incredibly large backwards compatibility break compared
to 2.0.0a13.

> Important: if you have written your own XSL based template; please change the template.xml to generate the XML output
> first by adding a transformation with the XmlAst writer. Please look at our existing templates for an example.

> Important: please open an issue for every broken piece of functionality. We have taken the utmost care not to
> introduce bugs but due to large change some might occur.

In the following list we have tried to exhaustively document the list of changes and their impact. Due to the size of
the refactoring it is nigh impossible to make a 100% accurate list. Please contact us if you have questions; the website
http://www.phpdoc.org describes various methods.

```
ADDED      Twig Writer
ADDED      Xml Writer
ADDED      Router component to provide locations for various Url Schemes
ADDED      Responsive-twig template
ADDED      More tests
ADDED      Statistics are being logged to a log file (more will be logged there in the future)
ADDED      Compiler component that adds the ability to inject compiler passes at various points
ADDED      Linker to create object links between the various elements
ADDED      Indexes containing pre-generated lists to make the generation of templates faster
ADDED      More unit tests
FIXED      Define transformations in phpdoc.xml
FIXED      Define multiple templates in phpdoc.xml
FIXED      Performance is improved by re-approaching inheritance from a different angle
FIXED      Various small and unnamed bugs
CHANGED    Moved Inheritance from a Behaviour into the Descriptors
CHANGED    Temporarily removed deprecated report
CHANGED    Target option of parser now only accepts a folder
CHANGED    Various performance improvements
CHANGED    Doctrine Support is moved to its own Service Provider
CHANGED    Rewired dependencies and injection scheme to make better of the DIC
CHANGED    Object graph is written to various cache folders in a directory named 'build' by default
CHANGED    Cache is generated by Zend\Cache
CHANGED    Commands have been moved to their respective component
CHANGED    The Parser has been promoted to Service Provider
CHANGED    The Transformer has been promoted to Service Provider
CHANGED    Parse command has been prepared for internationalization
CHANGED    Template configuration is parsed using JmsSerializer
CHANGED    All logging is now PSR-3 compliant
CHANGED    Removed documentation for components that are still in flux
CHANGED    Updated documentation for existing functionality
DEPRECATED Behaviours are only executed for the XmlAst writer
BC-BREAK   Moved Validators to the Descriptor Builder and refactored for internationalization
BC-BREAK   Parser generates an object graph and not XML (structure.xml, use XmlAst writer now for XML output)
BC-BREAK   Replaced plugin system with Service Providers
```