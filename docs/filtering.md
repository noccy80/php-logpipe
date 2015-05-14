Filtering
=========


## Using exclude or include

Dump all but specified channels (exclude):

    $ logpipe dump -x channel.name,other.channel
    
Only dump specified channels (include):

    $ logpipe dump -i channel.name,other.channeö

### Technical details

This is implemented using the `BasicFilter`

## Using expressionlanguage

Only dump specific channels:

    $ logpipe dump -f "message.channel in [ 'channel.name', 'other.channel' ]"

### Technical details

This is implemented using the `ExpressionFilter`
